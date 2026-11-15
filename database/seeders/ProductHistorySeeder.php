<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\HistoryService;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class ProductHistorySeeder extends Seeder
{
    use SeedsDemoData;

    /**
     * Historial inicial de productos: alta, actualizaciones y cambios de foto.
     * Los movimientos de entrada/salida se generan en ProductMovementSeeder.
     */
    public function run(): void
    {
        $history = app(HistoryService::class);

        $products = Product::query()->withTrashed()->get();

        foreach ($products as $product) {
            $createdAt = $product->created_at ?? now()->subMonths(6);

            $created = $history->record(
                $product,
                'created',
                'Producto creado: '.$product->product_code,
            );
            $this->backdate($created, $createdAt);

            if (fake()->boolean(45)) {
                $updatedAt = $this->randomDate($createdAt->copy()->addMinute(), now());

                $updated = $history->record(
                    $product,
                    'updated',
                    'Producto actualizado: '.$product->product_code,
                );
                $this->backdate($updated, $updatedAt);
            }

            if (fake()->boolean(20)) {
                $photoAt = $this->randomDate($createdAt->copy()->addMinute(), now());

                $photo = $history->record(
                    $product,
                    'photo_updated',
                    'Foto del producto actualizada',
                );
                $this->backdate($photo, $photoAt);
            }
        }
    }
}
