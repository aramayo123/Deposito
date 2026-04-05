<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\HistoryActionLabels;
use App\Models\ProductEntry;
use App\Models\ProductExit;
use App\Models\ProductHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function global(Request $request): View
    {
        return view('reports.global');
    }

    public function apiSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        $type = $request->get('type', 'product');
        $from = $request->get('from');
        $to = $request->get('to');
        $movement = $request->get('movement');

        $products = collect();
        $entries = collect();
        $exits = collect();
        $history = collect();

        if ($q === '') {
            return response()->json([
                'products' => [],
                'entries' => [],
                'exits' => [],
                'history' => [],
            ]);
        }

        if (in_array($type, ['product', 'technician', 'license_plate', 'entry_code', 'exit_code'], true)) {
            if ($type === 'product') {
                $products = Product::query()
                    ->select(['id', 'product_code', 'name', 'available_quantity', 'damaged_quantity', 'minimum_stock'])
                    ->where(function ($qq) use ($q) {
                        $qq->where('product_code', 'like', '%'.$q.'%')
                            ->orWhere('name', 'like', '%'.$q.'%');
                    })
                    ->limit(25)
                    ->get();
            }

            if ($type === 'entry_code') {
                $entries = ProductEntry::query()
                    ->select(['id', 'entry_code', 'entry_date', 'entry_time', 'notes'])
                    ->where('entry_code', 'like', '%'.$q.'%')
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            } elseif ($type === 'product') {
                $entries = ProductEntry::query()
                    ->select(['id', 'entry_code', 'entry_date', 'entry_time', 'notes'])
                    ->whereHas('items.product', function ($qq) use ($q) {
                        $qq->where('product_code', 'like', '%'.$q.'%')
                            ->orWhere('name', 'like', '%'.$q.'%');
                    })
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            }

            if ($type === 'exit_code') {
                $exits = ProductExit::query()
                    ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'notes'])
                    ->where('exit_code', 'like', '%'.$q.'%')
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            } elseif ($type === 'technician') {
                $exits = ProductExit::query()
                    ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'notes'])
                    ->where('technician_name', 'like', '%'.$q.'%')
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            } elseif ($type === 'license_plate') {
                $exits = ProductExit::query()
                    ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'notes'])
                    ->where('license_plate', 'like', '%'.$q.'%')
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            } elseif ($type === 'product') {
                $exits = ProductExit::query()
                    ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'license_plate', 'is_for_workshop', 'notes'])
                    ->whereHas('items.product', function ($qq) use ($q) {
                        $qq->where('product_code', 'like', '%'.$q.'%')
                            ->orWhere('name', 'like', '%'.$q.'%');
                    })
                    ->withCount('items')
                    ->limit(25)
                    ->get();
            }

            $historyQuery = ProductHistory::query()
                ->select(['id', 'product_id', 'action_type', 'description', 'technician_name', 'license_plate', 'quantity_change', 'quantity_before', 'quantity_after', 'created_at'])
                ->with(['product' => fn ($pq) => $pq->select(['id', 'product_code', 'name'])]);

            if ($type === 'product') {
                $historyQuery->whereHas('product', function ($qq) use ($q) {
                    $qq->where('product_code', 'like', '%'.$q.'%')
                        ->orWhere('name', 'like', '%'.$q.'%');
                });
            } elseif ($type === 'technician') {
                $historyQuery->where('technician_name', 'like', '%'.$q.'%');
            } elseif ($type === 'license_plate') {
                $historyQuery->where('license_plate', 'like', '%'.$q.'%');
            } elseif ($type === 'entry_code') {
                $entryIds = ProductEntry::query()->where('entry_code', 'like', '%'.$q.'%')->pluck('id');
                $historyQuery->where('reference_type', 'ProductEntry')->whereIn('reference_id', $entryIds);
            } elseif ($type === 'exit_code') {
                $exitIds = ProductExit::query()->where('exit_code', 'like', '%'.$q.'%')->pluck('id');
                $historyQuery->where('reference_type', 'ProductExit')->whereIn('reference_id', $exitIds);
            }

            if ($from) {
                $historyQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $historyQuery->whereDate('created_at', '<=', $to);
            }
            if ($movement) {
                $map = ['entrada' => 'entry', 'salida' => 'exit', 'modificacion' => 'updated'];
                $at = $map[$movement] ?? $movement;
                $historyQuery->where('action_type', $at);
            }

            $history = $historyQuery->orderByDesc('created_at')->limit(50)->get();
        }

        $historyOut = $history->map(function (ProductHistory $row) {
            return array_merge($row->toArray(), [
                'action_label_es' => HistoryActionLabels::spanish($row->action_type),
                'action_display' => HistoryActionLabels::forDisplay($row->action_type),
            ]);
        })->values();

        return response()->json([
            'products' => $products,
            'entries' => $entries,
            'exits' => $exits,
            'history' => $historyOut,
        ]);
    }
}
