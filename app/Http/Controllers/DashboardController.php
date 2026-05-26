<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductExit;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $metrics = Cache::remember('dashboard.metrics', 60, function () {
            $today = now()->toDateString();

            return [
                'products_count' => Product::query()->count(),
                'available_sum' => (float) Product::query()->sum('available_quantity'),
                'damaged_sum' => (float) Product::query()->sum('damaged_quantity'),
                'low_stock_count' => Product::query()->lowStock()->count(),
                'entries_today' => ProductEntry::query()->whereDate('entry_date', $today)->count(),
                'exits_today' => ProductExit::query()->whereDate('exit_date', $today)->count(),
            ];
        });

        $chart = Cache::remember('dashboard.movements_chart', 60, function () {
            $labels = [];
            $entries = [];
            $exits = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i)->toDateString();
                $labels[] = now()->subDays($i)->format('d/m');
                $entries[] = ProductEntry::query()->whereDate('entry_date', $d)->count();
                $exits[] = ProductExit::query()->whereDate('exit_date', $d)->count();
            }

            return compact('labels', 'entries', 'exits');
        });

        $alerts = StockAlert::query()
            ->select(['id', 'product_id', 'alert_type', 'current_quantity', 'minimum_stock', 'is_read', 'created_at'])
            ->with(['product' => fn ($q) => $q->select(['id', 'product_code', 'name'])])
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentEntries = ProductEntry::query()
            ->select(['id', 'entry_code', 'entry_date', 'entry_time', 'created_at'])
            ->withCount('items')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $recentExits = ProductExit::query()
            ->select(['id', 'exit_code', 'exit_date', 'exit_time', 'technician_name', 'is_for_workshop', 'created_at'])
            ->withCount('items')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('metrics', 'chart', 'alerts', 'recentEntries', 'recentExits'));
    }
}
