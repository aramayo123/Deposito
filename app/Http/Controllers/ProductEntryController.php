<?php

namespace App\Http\Controllers;

use App\Events\ProductMovementEvent;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Resources\EntryResource;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductEntryItem;
use App\Services\HistoryService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductEntryController extends Controller
{
    public function __construct(
        protected HistoryService $historyService,
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request): View
    {
        $query = ProductEntry::query()
            ->select(['id', 'entry_code', 'entry_date', 'entry_time', 'notes', 'created_at'])
            ->withCount('items')
            ->with(['items' => fn ($q) => $q->select(['id', 'product_entry_id', 'product_id'])->with(['product' => fn ($pq) => $pq->select(['id', 'product_code', 'name'])])]);

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->get('to'));
        }
        if ($code = trim((string) $request->get('entry_code'))) {
            $query->where('entry_code', 'like', '%'.$code.'%');
        }
        if ($pq = trim((string) $request->get('product'))) {
            $query->whereHas('items.product', function ($q) use ($pq) {
                $q->where('product_code', 'like', '%'.$pq.'%')
                    ->orWhere('name', 'like', '%'.$pq.'%');
            });
        }

        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('entries.index', compact('entries'));
    }

    public function create(Request $request): View
    {
        return view('entries.create', [
            'presetProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(StoreEntryRequest $request): RedirectResponse|JsonResponse
    {
        $entry = DB::transaction(function () use ($request) {
            $entry = ProductEntry::query()->create([
                'entry_date' => $request->validated('entry_date'),
                'entry_time' => $request->validated('entry_time'),
                'notes' => $request->validated('notes'),
                'created_by' => $request->input('created_by') ?: 'Sistema',
            ]);

            foreach ($request->validated('items') as $row) {
                $productId = (int) $row['product_id'];
                $qtyRec = (float) $row['quantity_received'];
                $qtyDam = (float) ($row['quantity_damaged'] ?? 0);
                $good = $qtyRec - $qtyDam;

                ProductEntryItem::query()->create([
                    'product_entry_id' => $entry->id,
                    'product_id' => $productId,
                    'quantity_received' => $qtyRec,
                    'quantity_damaged' => $qtyDam,
                    'damage_notes' => $row['damage_notes'] ?? null,
                ]);

                $product = Product::query()->lockForUpdate()->findOrFail($productId);
                $beforeA = (float) $product->available_quantity;
                $beforeD = (float) $product->damaged_quantity;
                $afterA = $beforeA + $good;
                $afterD = $beforeD + $qtyDam;
                $product->update([
                    'available_quantity' => $afterA,
                    'damaged_quantity' => $afterD,
                ]);

                $this->historyService->recordEntry(
                    $product->fresh(),
                    $entry->id,
                    $good,
                    $qtyDam,
                    $beforeA,
                    $afterA,
                    $beforeD,
                    $afterD,
                );
                $this->notificationService->syncStockAlertsAndDispatch($product->fresh());
            }

            return $entry->load(['items.product']);
        });

        event(new ProductMovementEvent(
            'entry',
            $entry->entry_code,
            'Entrada registrada',
            'Ticket '.$entry->entry_code.' registrado correctamente.'
        ));

        $this->notificationService->clearDashboardCache();

        if ($request->expectsJson()) {
            return (new EntryResource($entry))->response()->setStatusCode(201);
        }

        return redirect()->route('entries.show', $entry)->with('success', 'Entrada registrada correctamente.');
    }

    public function show(ProductEntry $entry): View
    {
        $entry->load(['items' => fn ($q) => $q->with(['product' => fn ($pq) => $pq->select(['id', 'product_code', 'name', 'photo', 'available_quantity', 'damaged_quantity'])])]);

        return view('entries.show', compact('entry'));
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $query = ProductEntry::query()
            ->select(['id', 'entry_code', 'entry_date', 'entry_time', 'notes', 'created_by', 'created_at'])
            ->withCount('items');

        if ($request->filled('from')) {
            $query->whereDate('entry_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('entry_date', '<=', $request->get('to'));
        }

        return EntryResource::collection($query->orderByDesc('entry_date')->paginate(25))->response();
    }
}
