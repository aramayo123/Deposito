<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductEntryItem;
use App\Models\ProductExit;
use App\Models\ProductExitItem;
use App\Models\StockAlert;
use App\Services\HistoryService;
use App\Services\NotificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(ProductSeeder::class);

        $depo1 = Deposit::query()->create(['name' => 'Taller Mecánico']);
        $depo2 = Deposit::query()->create(['name' => 'Pañol Eléctrico']);
        $depo3 = Deposit::query()->create(['name' => 'Depósito Obra Norte']);
        $deposits = [$depo1, $depo2, $depo3];

        $history = app(HistoryService::class);
        $notif = app(NotificationService::class);

        $technicians = ['Diego López', 'María García', 'Carlos Ruiz', 'Ana Martínez'];

        for ($e = 0; $e < 20; $e++) {
            DB::transaction(function () use ($history, $notif, $e) {
                $entry = ProductEntry::query()->create([
                    'entry_date' => now()->subDays(random_int(0, 60))->toDateString(),
                    'entry_time' => sprintf('%02d:%02d:00', random_int(8, 17), random_int(0, 59)),
                    'notes' => $e % 3 === 0 ? 'Recepción depósito central' : null,
                    'created_by' => 'Seeder',
                ]);
                $n = random_int(1, 4);
                for ($i = 0; $i < $n; $i++) {
                    $product = Product::query()->inRandomOrder()->lockForUpdate()->first();
                    if (! $product) {
                        return;
                    }
                    $rec = random_int(2, 15);
                    $dam = $i === 0 && $e % 5 === 0 ? min(2, $rec - 1) : 0;
                    $good = $rec - $dam;
                    ProductEntryItem::query()->create([
                        'product_entry_id' => $entry->id,
                        'product_id' => $product->id,
                        'quantity_received' => $rec,
                        'quantity_damaged' => $dam,
                        'damage_notes' => $dam > 0 ? 'Embalaje deteriorado' : null,
                    ]);
                    $beforeA = (float) $product->available_quantity;
                    $beforeD = (float) $product->damaged_quantity;
                    $product->update([
                        'available_quantity' => $beforeA + $good,
                        'damaged_quantity' => $beforeD + $dam,
                    ]);
                    $history->recordEntry(
                        $product->fresh(),
                        $entry->id,
                        $good,
                        $dam,
                        $beforeA,
                        $beforeA + $good,
                        $beforeD,
                        $beforeD + $dam,
                    );
                    $notif->syncStockAlertsAndDispatch($product->fresh());
                }
            });
        }

        for ($x = 0; $x < 35; $x++) {
            DB::transaction(function () use ($history, $notif, $x, $technicians, $deposits) {
                $workshop = $x >= 30;
                $exit = ProductExit::query()->create([
                    'exit_date' => now()->subDays(random_int(0, 45))->toDateString(),
                    'exit_time' => sprintf('%02d:%02d:00', random_int(8, 18), random_int(0, 59)),
                    'technician_name' => $workshop ? null : $technicians[array_rand($technicians)],
                    'deposit_id' => $workshop ? null : $deposits[array_rand($deposits)]->id,
                    'is_for_workshop' => $workshop,
                    'notes' => null,
                    'created_by' => 'Seeder',
                ]);
                $n = random_int(1, 3);
                for ($i = 0; $i < $n; $i++) {
                    $product = Product::query()->where('available_quantity', '>', 0)->inRandomOrder()->lockForUpdate()->first();
                    if (! $product) {
                        return;
                    }
                    $max = (float) $product->available_quantity;
                    $qty = min(5.0, floor($max));
                    $qty = max(1, $qty);
                    ProductExitItem::query()->create([
                        'product_exit_id' => $exit->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                    ]);
                    $beforeA = (float) $product->available_quantity;
                    $afterA = $beforeA - $qty;
                    $product->update(['available_quantity' => $afterA]);
                    $history->recordExit(
                        $product->fresh(),
                        $exit->id,
                        $qty,
                        $beforeA,
                        $afterA,
                        $exit->technician_name,
                        $exit->deposit_id,
                    );
                    $notif->syncStockAlertsAndDispatch($product->fresh());
                }
            });
        }

        Product::query()->lowStock()->inRandomOrder()->limit(8)->get()->each(function (Product $p) {
            StockAlert::query()->firstOrCreate(
                [
                    'product_id' => $p->id,
                    'alert_type' => (float) $p->available_quantity <= 0 ? 'out_of_stock' : 'low_stock',
                    'is_read' => false,
                ],
                [
                    'current_quantity' => $p->available_quantity,
                    'minimum_stock' => $p->minimum_stock,
                ]
            );
        });

        $notif->clearDashboardCache();
    }
}
