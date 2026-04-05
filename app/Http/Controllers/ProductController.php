<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\HistoryActionLabels;
use App\Models\ProductHistory;
use App\Services\HistoryService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected HistoryService $historyService,
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): View
    {
        $allowedSorts = ['product_code', 'name', 'available_quantity', 'damaged_quantity', 'minimum_stock', 'created_at'];
        $sort = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'product_code';
        $dir = strtolower((string) $request->get('dir')) === 'desc' ? 'desc' : 'asc';

        $query = Product::query()
            ->select(['id', 'product_code', 'name', 'photo', 'available_quantity', 'damaged_quantity', 'minimum_stock', 'is_active', 'created_at', 'deleted_at'])
            ->with(['stockAlerts' => fn ($q) => $q->select(['id', 'product_id', 'alert_type', 'is_read'])->where('is_read', false)]);

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        match ($request->get('stock')) {
            'low' => $query->lowStock(),
            'out' => $query->where('available_quantity', '<=', 0),
            'damaged' => $query->withDamaged(),
            default => null,
        };

        $products = $query->orderBy($sort, $dir)->paginate(25)->withQueryString();

        return view('products.index', compact('products', 'sort', 'dir'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::query()->create($data);
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('products', 'public');
                $product->update(['photo' => $path]);
            }
            $this->historyService->record(
                $product,
                'created',
                'Producto creado: '.$product->product_code,
            );
            $this->notificationService->syncStockAlertsAndDispatch($product->fresh());

            return $product->fresh(['stockAlerts']);
        });

        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return (new ProductResource($product))->response()->setStatusCode(201);
        }

        return redirect()->route('products.show', $product)->with('success', 'Producto creado.');
    }

    public function show(Request $request, Product $product): View|JsonResponse
    {
        $product->load(['stockAlerts' => fn ($q) => $q->where('is_read', false)]);

        $historyQuery = $product->histories()->orderByDesc('created_at');
        if ($request->filled('h_action')) {
            $historyQuery->where('action_type', $request->get('h_action'));
        }
        if ($request->filled('h_from')) {
            $historyQuery->whereDate('created_at', '>=', $request->get('h_from'));
        }
        if ($request->filled('h_to')) {
            $historyQuery->whereDate('created_at', '<=', $request->get('h_to'));
        }
        $history = $historyQuery->paginate(25)->withQueryString();

        return view('products.show', compact('product', 'history'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);
            if ($request->hasFile('photo')) {
                if ($product->photo) {
                    Storage::disk('public')->delete($product->photo);
                }
                $path = $request->file('photo')->store('products', 'public');
                $product->update(['photo' => $path]);
                $this->historyService->record(
                    $product,
                    'photo_updated',
                    'Foto del producto actualizada',
                );
            }
            $this->historyService->record(
                $product,
                'updated',
                'Producto actualizado: '.$product->product_code,
            );
            $this->notificationService->syncStockAlertsAndDispatch($product->fresh());
        });

        $product->refresh();
        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return (new ProductResource($product))->response();
        }

        return redirect()->route('products.show', $product)->with('success', 'Producto actualizado.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->delete();
        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }

    public function updatePhoto(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($product->photo) {
            Storage::disk('public')->delete($product->photo);
        }
        $path = $request->file('photo')->store('products', 'public');
        $product->update(['photo' => $path]);
        $this->historyService->record(
            $product,
            'photo_updated',
            'Foto del producto actualizada',
        );

        if ($request->expectsJson()) {
            return (new ProductResource($product->fresh()))->response();
        }

        return back()->with('success', 'Foto actualizada.');
    }

    public function updateDamaged(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $request->validate([
            'damaged_quantity' => 'required|integer|min:0',
        ]);
        $newDamaged = (int) $request->input('damaged_quantity');
        $oldDamaged = (int) $product->damaged_quantity;
        $delta = $newDamaged - $oldDamaged;
        $beforeAvailable = (int) $product->available_quantity;
        $afterAvailable = $beforeAvailable - $delta;
        if ($afterAvailable < 0) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No hay suficientes unidades disponibles para marcar como dañadas.'], 422);
            }

            return back()->withErrors(['damaged_quantity' => 'No hay suficientes unidades disponibles.'])->withInput();
        }

        DB::transaction(function () use ($product, $newDamaged, $delta, $oldDamaged, $beforeAvailable, $afterAvailable) {
            $beforeDamaged = $oldDamaged;
            $product->update([
                'damaged_quantity' => $newDamaged,
                'available_quantity' => $afterAvailable,
            ]);
            $afterDamaged = (int) $product->damaged_quantity;
            $this->historyService->recordDamaged(
                $product,
                $delta,
                $beforeAvailable,
                $afterAvailable,
                $beforeDamaged,
                $afterDamaged,
            );
            $this->notificationService->syncStockAlertsAndDispatch($product->fresh());
        });

        $product->refresh();
        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return (new ProductResource($product))->response();
        }

        return back()->with('success', 'Cantidad dañada actualizada.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $query = Product::query()
            ->select(['id', 'product_code', 'name', 'photo', 'available_quantity', 'damaged_quantity', 'minimum_stock', 'is_active']);

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($qq) use ($q) {
                $qq->where('product_code', 'like', '%'.$q.'%')
                    ->orWhere('name', 'like', '%'.$q.'%');
            });
        }

        $perPage = min(100, max(1, (int) $request->get('per_page', 25)));

        return ProductResource::collection($query->orderBy('product_code')->paginate($perPage))->response();
    }

    public function checkCode(Request $request, string $code): JsonResponse
    {
        $query = Product::query()->where('product_code', $code);
        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', (int) $request->get('exclude_id'));
        }

        return response()->json(['available' => ! $query->exists()]);
    }

    public function apiHistory(Request $request, Product $product): JsonResponse
    {
        $q = $product->histories()->orderByDesc('created_at');
        if ($request->filled('action_type')) {
            $q->where('action_type', $request->get('action_type'));
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->get('to'));
        }

        $items = $q->get(['id', 'product_id', 'action_type', 'reference_type', 'reference_id', 'description', 'technician_name', 'license_plate', 'quantity_change', 'quantity_before', 'quantity_after', 'created_at']);

        return response()->json($items->map(function ($row) {
            return array_merge($row->toArray(), [
                'action_label_es' => HistoryActionLabels::spanish($row->action_type),
                'action_display' => HistoryActionLabels::forDisplay($row->action_type),
            ]);
        }));
    }
}
