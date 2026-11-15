<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockAlert;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class StockAlertSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $products = Product::query()
            ->whereColumn('available_quantity', '<=', 'minimum_stock')
            ->inRandomOrder()
            ->get();

        foreach ($products as $product) {
            $available = (float) $product->available_quantity;
            $minimum = (float) $product->minimum_stock;
            $alertType = $available <= 0 ? 'out_of_stock' : 'low_stock';

            $alert = StockAlert::query()->create([
                'product_id' => $product->id,
                'alert_type' => $alertType,
                'current_quantity' => $available,
                'minimum_stock' => $minimum,
                'is_read' => fake()->boolean(35),
            ]);
            $this->backdate(
                $alert,
                now()->subMinutes(fake()->numberBetween(5, 60 * 24 * 14)),
            );

            // Algunas alertas históricas ya leídas para variar el dashboard.
            if (fake()->boolean(30)) {
                $readAt = $this->randomDate(
                    $alert->created_at->copy()->addMinute(),
                    now(),
                );

                $readAlert = StockAlert::query()->create([
                    'product_id' => $product->id,
                    'alert_type' => $alertType,
                    'current_quantity' => $available,
                    'minimum_stock' => $minimum,
                    'is_read' => true,
                ]);
                $this->backdate($readAlert, $readAt);
            }
        }
    }
}
