<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepositRequest;
use App\Models\Deposit;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductExit;
use App\Models\ProductHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function index(Request $request): View
    {
        $query = Deposit::query()
            ->select(['id', 'name', 'created_at']);

        if ($search = trim((string) $request->get('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $deposits = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('deposits.index', compact('deposits'));
    }

    public function create(): View
    {
        return view('deposits.create');
    }

    public function store(StoreDepositRequest $request): RedirectResponse
    {
        $deposit = Deposit::query()->create($request->validated());

        return redirect()->route('deposits.show', $deposit)->with('success', 'Depósito creado correctamente.');
    }

    public function show(Request $request, Deposit $deposit): View
    {
        $exits = ProductExit::query()
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'is_for_workshop', 'notes', 'created_at'])
            ->where('deposit_id', $deposit->id)
            ->withCount('items')
            ->orderByDesc('exit_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $history = ProductHistory::query()
            ->select(['id', 'product_id', 'action_type', 'description', 'quantity_change', 'quantity_before', 'quantity_after', 'created_at'])
            ->where('deposit_id', $deposit->id)
            ->with(['product' => fn ($q) => $q->select(['id', 'product_code', 'name'])])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $totalExitsCount = ProductExit::query()->where('deposit_id', $deposit->id)->count();

        $exitProductIds = \App\Models\ProductExitItem::query()
            ->whereHas('exit', fn ($q) => $q->where('deposit_id', $deposit->id))
            ->pluck('product_id')
            ->unique();

        $totalProductsSent = $exitProductIds->count();

        return view('deposits.show', compact('deposit', 'exits', 'history', 'totalExitsCount', 'totalProductsSent'));
    }

    public function edit(Deposit $deposit): View
    {
        return view('deposits.edit', compact('deposit'));
    }

    public function update(StoreDepositRequest $request, Deposit $deposit): RedirectResponse
    {
        $deposit->update($request->validated());

        return redirect()->route('deposits.show', $deposit)->with('success', 'Depósito actualizado correctamente.');
    }

    public function destroy(Request $request, Deposit $deposit): RedirectResponse
    {
        $deposit->delete();

        return redirect()->route('deposits.index')->with('success', 'Depósito eliminado correctamente.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $query = Deposit::query()->select(['id', 'name', 'created_at']);

        if ($q = trim((string) $request->get('q'))) {
            $query->where('name', 'like', '%'.$q.'%');
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }
}
