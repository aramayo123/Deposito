<?php

namespace App\Http\Controllers;

use App\Events\ProductMovementEvent;
use App\Http\Requests\StoreExitRequest;
use App\Http\Resources\ExitResource;
use App\Models\Deposit;
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
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'deposit_id', 'is_for_workshop', 'created_at'])
            ->with('deposit')
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
        if ($d = trim((string) $request->get('deposit'))) {
            $query->whereHas('deposit', function ($q) use ($d) {
                $q->where('name', 'like', '%'.$d.'%');
            });
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
        $deposits = Deposit::query()->select(['id', 'name'])->orderBy('name')->get();

        return view('exits.create', [
            'presetProductId' => $request->integer('product_id') ?: null,
            'deposits' => $deposits,
        ]);
    }

    public function store(StoreExitRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $processed = [];
        $skipped = [];

        DB::beginTransaction();

        try {
            $exit = ProductExit::query()->create([
                ...$data,
                'technician_name' => $data['is_for_workshop'] ? null : ($data['technician_name'] ?? null),
                'deposit_id' => $data['is_for_workshop'] ? null : ($data['deposit_id'] ?? null),
                'created_by' => $request->input('created_by') ?: 'Sistema',
            ]);

            $anyProcessed = false;

            foreach ($items as $row) {
                $productId = (int) $row['product_id'];
                $qty = (float) $row['quantity'];

                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $available = (float) $product->available_quantity;

                $code = $product->product_code;
                $name = $product->name;

                if ($qty > $available) {
                    $skipped[] = [
                        'code' => $code,
                        'name' => $name ?? '',
                        'requested' => $qty,
                        'available' => $available,
                    ];
                    continue;
                }

                ProductExitItem::query()->create([
                    'product_exit_id' => $exit->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                ]);

                $beforeA = $available;
                $afterA = $beforeA - $qty;
                $product->update(['available_quantity' => $afterA]);

                $product->refresh();
                $this->historyService->recordExit(
                    $product,
                    $exit->id,
                    $qty,
                    $beforeA,
                    $afterA,
                    $exit->technician_name,
                    $exit->deposit_id,
                );
                $this->notificationService->syncStockAlertsAndDispatch($product);

                $processed[] = [
                    'code' => $code,
                    'name' => $name ?? '',
                    'quantity' => $qty,
                ];

                $anyProcessed = true;
            }

            if (! $anyProcessed) {
                DB::rollBack();

                session()->flash('exit_skipped_all', $skipped);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Ningún ítem tiene stock suficiente.',
                        'processed' => [],
                        'skipped' => $skipped,
                    ], 422);
                }

                return redirect()->route('exits.create')
                    ->with('exit_skipped_all', $skipped)
                    ->with('error', 'Ningún ítem tiene stock suficiente. No se pudo registrar la salida.')
                    ->withInput();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $exit->load(['items.product', 'deposit']);

        event(new ProductMovementEvent(
            'exit',
            $exit->exit_code,
            'Salida registrada',
            'Ticket '.$exit->exit_code.' registrado correctamente.'
        ));

        $this->notificationService->clearDashboardCache();

        session()->flash('exit_processed', $processed);
        session()->flash('exit_skipped', $skipped);

        if ($request->expectsJson()) {
            return (new ExitResource($exit))->response()->setStatusCode(201);
        }

        return redirect()->route('exits.show', $exit)->with('success', 'Salida registrada correctamente.');
    }

    public function show(ProductExit $product_exit): View
    {
        $product_exit->load(['items' => fn ($q) => $q->with(['product' => fn ($pq) => $pq->select(['id', 'product_code', 'name', 'photo', 'available_quantity', 'damaged_quantity'])]), 'deposit']);

        return view('exits.show', ['exit' => $product_exit]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $query = ProductExit::query()
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'deposit_id', 'is_for_workshop', 'notes', 'created_at'])
            ->with('deposit')
            ->withCount('items');

        if ($request->filled('technician')) {
            $query->where('technician_name', 'like', '%'.$request->get('technician').'%');
        }
        if ($request->filled('deposit_id')) {
            $query->where('deposit_id', $request->get('deposit_id'));
        }

        return ExitResource::collection($query->orderByDesc('exit_date')->paginate(25))->response();
    }
}
