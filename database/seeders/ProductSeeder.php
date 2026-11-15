<?php

namespace Database\Seeders;

use App\Models\Product;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $start = now()->subMonths(6)->startOfDay();

        $categories = [
            'TAL' => 'Taladro',
            'AMO' => 'Amoladora',
            'HID' => 'Hidrolavadora',
            'MEC' => 'Juego de mechas',
            'TOR' => 'Tornillos',
            'CIN' => 'Cinta métrica',
            'LLA' => 'Juego de llaves',
            'DIS' => 'Disco de corte',
            'PIN' => 'Pinza',
            'SOL' => 'Soldadora',
            'COM' => 'Compresor',
            'SEG' => 'Elemento de seguridad',
        ];

        $keys = array_keys($categories);

        for ($i = 0; $i < 60; $i++) {
            $category = $keys[$i % count($keys)];
            $productCode = $category.'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            $available = match (true) {
                $i % 13 === 0 => 0.0,
                $i % 7 === 0 => round(fake()->randomFloat(3, 0.5, 5), 3),
                $i % 5 === 0 => round(fake()->randomFloat(3, 8, 18), 3),
                default => round(fake()->randomFloat(2, 20, 140), 3),
            };

            $minimumStock = round(fake()->randomFloat(2, 4, 25), 3);
            $damaged = fake()->boolean(35)
                ? round(fake()->randomFloat(2, 0.5, 6), 3)
                : 0.0;

            $createdAt = $this->randomDate(
                $start,
                $start->copy()->addDays(45)->setTime(23, 59, 59),
            );

            $product = Product::query()->create([
                'product_code' => $productCode,
                'name' => Str::title($categories[$category].' '.fake()->words(2, true)),
                'photo' => null,
                'available_quantity' => $available,
                'damaged_quantity' => $damaged,
                'minimum_stock' => $minimumStock,
                'is_active' => $i % 17 !== 0,
            ]);

            $this->backdate($product, $createdAt);
        }

        // Algunos productos eliminados lógicamente para cubrir SoftDeletes.
        for ($i = 1; $i <= 3; $i++) {
            $createdAt = $this->randomDate($start, $start->copy()->addDays(30));

            $product = Product::query()->create([
                'product_code' => 'OLD-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Producto discontinuado '.$i,
                'photo' => null,
                'available_quantity' => 0,
                'damaged_quantity' => 0,
                'minimum_stock' => 5,
                'is_active' => false,
            ]);

            $this->backdate($product, $createdAt);
            $product->delete();
            $product->forceFill(['deleted_at' => $this->randomDate($createdAt, now())])->saveQuietly();
        }
    }
}
