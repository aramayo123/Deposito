<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    public function apiIndex(Request $request): JsonResponse
    {
        $query = StockAlert::query()
            ->select(['id', 'product_id', 'alert_type', 'current_quantity', 'minimum_stock', 'is_read', 'created_at'])
            ->with(['product' => fn ($q) => $q->select(['id', 'product_code', 'name'])])
            ->where('is_read', false)
            ->orderByDesc('created_at');

        return response()->json(
            $query->get()->map(fn (StockAlert $a) => [
                'id' => $a->id,
                'product_id' => $a->product_id,
                'alert_type' => $a->alert_type,
                'current_quantity' => $a->current_quantity,
                'minimum_stock' => $a->minimum_stock,
                'is_read' => (bool) $a->is_read,
                'product_code' => $a->product?->product_code,
                'product_name' => $a->product?->name,
            ])
        );
    }

    public function markRead(Request $request, StockAlert $stockAlert): JsonResponse|RedirectResponse
    {
        $stockAlert->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'is_read' => true]);
        }

        return back()->with('success', 'Alerta marcada como leída.');
    }
}
