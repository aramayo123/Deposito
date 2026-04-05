<?php

namespace App\Http\Controllers;

use App\Events\ProductMovementEvent;
use App\Http\Requests\StoreExitRequest;
use App\Http\Resources\ExitResource;
use App\Models\Product;
use App\Models\ProductExit;
use App\Models\ProductExitItem;
use App\Services\HistoryService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductExitController extends Controller
{
    public function __construct(
        protected HistoryService $historyService,
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): View
    {
        $query = ProductExit::query()
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'created_at'])
            ->withCount('items');

        if ($request->filled('from')) {
            $query->whereDate('exit_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('exit_date', '<=', $request->get('to'));
        }
        if ($t = trim((string) $request->get('technician'))) {
            $query->where('technician_name', 'like', '%'.$t.'%');
        }
        if ($p = trim((string) $request->get('license_plate'))) {
            $query->where('license_plate', 'like', '%'.$p.'%');
        }
        if ($pc = trim((string) $request->get('product'))) {
            $query->whereHas('items.product', function ($q) use ($pc) {
                $q->where('product_code', 'like', '%'.$pc.'%')
                    ->orWhere('name', 'like', '%'.$pc.'%');
            });
        }

        $exits = $query->orderByDesc('exit_date')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('exits.index', compact('exits'));
    }

    public function create(Request $request): View
    {
        return view('exits.create', [
            'presetProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(StoreExitRequest $request): RedirectResponse|JsonResponse
    {
        $exit = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $exit = ProductExit::query()->create([
                ...$data,
                'technician_name' => $data['is_for_workshop'] ? null : ($data['technician_name'] ?? null),
                'license_plate' => $data['is_for_workshop'] ? null : ($data['license_plate'] ?? null),
                'created_by' => $request->input('created_by') ?: 'Sistema',
            ]);

            foreach ($items as $row) {
                $productId = (int) $row['product_id'];
                $qty = (int) $row['quantity'];

                ProductExitItem::query()->create([
                    'product_exit_id' => $exit->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                ]);

                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $beforeA = (int) $product->available_quantity;
                $afterA = $beforeA - $qty;
                $product->update(['available_quantity' => $afterA]);

                $this->historyService->recordExit(
                    $product->fresh(),
                    $exit->id,
                    $qty,
                    $beforeA,
                    $afterA,
                    $exit->technician_name,
                    $exit->license_plate,
                );
                $this->notificationService->syncStockAlertsAndDispatch($product->fresh());
            }

            return $exit->load(['items.product']);
        });

        event(new ProductMovementEvent(
            'exit',
            $exit->exit_code,
            'Salida registrada',
            'Ticket '.$exit->exit_code.' registrado correctamente.'
        ));

        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return (new ExitResource($exit))->response()->setStatusCode(201);
        }

        return redirect()->route('exits.show', $exit)->with('success', 'Salida registrada.');
    }

    public function show(ProductExit $product_exit): View
    {
        $product_exit->load(['items' => fn ($q) => $q->with(['product' => fn ($pq) => $pq->select(['id', 'product_code', 'name', 'photo', 'available_quantity', 'damaged_quantity'])])]);

        return view('exits.show', ['exit' => $product_exit]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $query = ProductExit::query()
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'notes', 'created_at'])
            ->withCount('items');

        if ($request->filled('technician')) {
            $query->where('technician_name', 'like', '%'.$request->get('technician').'%');
        }
        if ($request->filled('license_plate')) {
            $query->where('license_plate', $request->get('license_plate'));
        }

        return ExitResource::collection($query->orderByDesc('exit_date')->paginate(25))->response();
    }
}
