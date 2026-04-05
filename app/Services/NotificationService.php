<?php

namespace App\Services;

use App\Events\LowStockEvent;
use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function clearDashboardCache(): void
    {
        Cache::forget('dashboard.metrics');
        Cache::forget('dashboard.movements_chart');
    }

    public function syncStockAlertsAndDispatch(Product $product): void
    {
        $available = (int) $product->available_quantity;
        $minimum = (int) $product->minimum_stock;

        if ($available > $minimum) {
            return;
        }

        $alertType = $available <= 0 ? 'out_of_stock' : 'low_stock';

        $exists = StockAlert::query()
            ->where('product_id', $product->id)
            ->where('alert_type', $alertType)
            ->where('is_read', false)
            ->exists();

        if ($exists) {
            return;
        }

        StockAlert::query()->create([
            'product_id' => $product->id,
            'alert_type' => $alertType,
            'current_quantity' => $available,
            'minimum_stock' => $minimum,
            'is_read' => false,
        ]);

        event(new LowStockEvent($product, $alertType));
    }
}
