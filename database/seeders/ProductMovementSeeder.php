<?php

namespace Database\Seeders;

use App\Models\Deposit;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductEntryItem;
use App\Models\ProductExit;
use App\Models\ProductExitItem;
use App\Models\User;
use App\Services\HistoryService;
use Carbon\Carbon;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;

class ProductMovementSeeder extends Seeder
{
    use SeedsDemoData;

    private array $depositIds = [];

    private array $operators = [];

    private array $technicians = [];

    public function run(): void
    {
        $history = app(HistoryService::class);

        $start = now()->subMonths(6)->startOfDay();
        $mainEnd = now()->subDays(2);

        $this->depositIds = Deposit::query()->pluck('id')->all();
        $this->operators = User::query()->pluck('name')->all() ?: ['Sistema'];

        $this->technicians = [
            'Diego López',
            'María García',
            'Carlos Ruiz',
            'Ana Martínez',
            'Javier Fernández',
            'Lucía Romero',
        ];
        for ($i = 0; $i < 6; $i++) {
            $this->technicians[] = fake()->name();
        }

        $operations = [];

        for ($i = 0; $i < 110; $i++) {
            $operations[] = [
                'type' => 'entry',
                'at' => $this->randomDate($start, $mainEnd),
            ];
        }

        for ($i = 0; $i < 140; $i++) {
            $operations[] = [
                'type' => 'exit',
                'at' => $this->randomDate($start, $mainEnd),
            ];
        }

        for ($i = 0; $i < 25; $i++) {
            $operations[] = [
                'type' => 'damage',
                'at' => $this->randomDate($start, $mainEnd),
            ];
        }

        // Movimientos de hoy para que el dashboard muestre actividad reciente.
        for ($i = 0; $i < 6; $i++) {
            $operations[] = [
                'type' => 'entry',
                'at' => $this->randomDate(now()->startOfDay(), now()),
            ];
            $operations[] = [
                'type' => 'exit',
                'at' => $this->randomDate(now()->startOfDay(), now()),
            ];
        }

        usort($operations, fn (array $a, array $b) => $a['at']->timestamp <=> $b['at']->timestamp);

        foreach ($operations as $operation) {
            if ($operation['type'] === 'entry') {
                $this->createEntry($operation['at'], $history);
            } elseif ($operation['type'] === 'exit') {
                $this->createExit($operation['at'], $history);
            } else {
                $this->createDamage($operation['at'], $history);
            }
        }

        // Salidas recientes que dejan algunos productos en estado bajo/agotado
        // para que existan alertas de stock reales.
        $this->drainLowStock($history);
    }

    private function createEntry(Carbon $at, HistoryService $history): void
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('created_at', '<=', $at)
            ->inRandomOrder()
            ->limit(fake()->numberBetween(1, 4))
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $entry = ProductEntry::query()->create([
            'entry_date' => $at->toDateString(),
            'entry_time' => $at->format('H:i:s'),
            'notes' => fake()->boolean(35) ? fake()->sentence(6) : null,
            'created_by' => $this->operators[array_rand($this->operators)],
        ]);
        $this->backdate($entry, $at);

        foreach ($products as $product) {
            $received = round(fake()->randomFloat(2, 1, 24), 3);
            $damaged = fake()->boolean(18)
                ? round(fake()->randomFloat(2, 0.5, max(0.5, $received * 0.2)), 3)
                : 0.0;
            $damaged = min($damaged, $received);
            $good = round($received - $damaged, 3);

            $item = ProductEntryItem::query()->create([
                'product_entry_id' => $entry->id,
                'product_id' => $product->id,
                'quantity_received' => $received,
                'quantity_damaged' => $damaged,
                'damage_notes' => $damaged > 0 ? fake()->sentence(4) : null,
            ]);
            $this->backdate($item, $at);

            $beforeAvailable = (float) $product->available_quantity;
            $beforeDamaged = (float) $product->damaged_quantity;
            $afterAvailable = round($beforeAvailable + $good, 3);
            $afterDamaged = round($beforeDamaged + $damaged, 3);

            $product->update([
                'available_quantity' => $afterAvailable,
                'damaged_quantity' => $afterDamaged,
            ]);

            $productHistory = $history->recordEntry(
                $product->fresh(),
                $entry->id,
                $good,
                $damaged,
                $beforeAvailable,
                $afterAvailable,
                $beforeDamaged,
                $afterDamaged,
            );
            $this->backdate($productHistory, $at);
        }
    }

    private function createExit(Carbon $at, HistoryService $history): void
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('created_at', '<=', $at)
            ->where('available_quantity', '>', 0)
            ->inRandomOrder()
            ->limit(fake()->numberBetween(1, 3))
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $isForWorkshop = fake()->boolean(20);
        $technicianName = $isForWorkshop
            ? null
            : $this->technicians[array_rand($this->technicians)];
        $depositId = $isForWorkshop || $this->depositIds === []
            ? null
            : $this->depositIds[array_rand($this->depositIds)];

        $exit = ProductExit::query()->create([
            'exit_date' => $at->toDateString(),
            'exit_time' => $at->format('H:i:s'),
            'technician_name' => $technicianName,
            'deposit_id' => $depositId,
            'is_for_workshop' => $isForWorkshop,
            'notes' => fake()->boolean(35) ? fake()->sentence(6) : null,
            'created_by' => $this->operators[array_rand($this->operators)],
        ]);
        $this->backdate($exit, $at);

        foreach ($products as $product) {
            $available = (float) $product->available_quantity;

            if ($available < 0.001) {
                continue;
            }

            $max = min(10.0, $available);
            $min = min(0.25, $max);
            $quantity = round(fake()->randomFloat(3, $min, $max), 3);
            $quantity = min($quantity, $available);

            if ($quantity < 0.001) {
                continue;
            }

            $item = ProductExitItem::query()->create([
                'product_exit_id' => $exit->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            $this->backdate($item, $at);

            $beforeAvailable = $available;
            $afterAvailable = round($beforeAvailable - $quantity, 3);

            $product->update(['available_quantity' => $afterAvailable]);

            $productHistory = $history->recordExit(
                $product->fresh(),
                $exit->id,
                $quantity,
                $beforeAvailable,
                $afterAvailable,
                $exit->technician_name,
                $exit->deposit_id,
            );
            $this->backdate($productHistory, $at);
        }
    }

    private function createDamage(Carbon $at, HistoryService $history): void
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('created_at', '<=', $at)
            ->where('available_quantity', '>', 0)
            ->inRandomOrder()
            ->first();

        if (! $product) {
            return;
        }

        $available = (float) $product->available_quantity;
        $damaged = (float) $product->damaged_quantity;

        $max = min(3.0, $available);
        $delta = round(fake()->randomFloat(3, min(0.25, $max), $max), 3);

        if ($delta < 0.001) {
            return;
        }

        $afterAvailable = round($available - $delta, 3);
        $afterDamaged = round($damaged + $delta, 3);

        $product->update([
            'available_quantity' => $afterAvailable,
            'damaged_quantity' => $afterDamaged,
        ]);

        $productHistory = $history->recordDamaged(
            $product->fresh(),
            $delta,
            $available,
            $afterAvailable,
            $damaged,
            $afterDamaged,
        );
        $this->backdate($productHistory, $at);
    }

    private function drainLowStock(HistoryService $history): void
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('available_quantity', '>', 0)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        foreach ($products as $product) {
            $available = (float) $product->available_quantity;

            if ($available < 0.001) {
                continue;
            }

            $minimum = (float) $product->minimum_stock;
            $target = fake()->boolean(35)
                ? 0.0
                : max(0.0, round($minimum - fake()->randomFloat(2, 0.5, 2), 3));

            $quantity = round($available - $target, 3);

            if ($quantity < 0.001) {
                continue;
            }

            $at = now()->subMinutes(fake()->numberBetween(5, 180));
            $isForWorkshop = fake()->boolean(25);
            $technicianName = $isForWorkshop
                ? null
                : $this->technicians[array_rand($this->technicians)];
            $depositId = $isForWorkshop || $this->depositIds === []
                ? null
                : $this->depositIds[array_rand($this->depositIds)];

            $exit = ProductExit::query()->create([
                'exit_date' => $at->toDateString(),
                'exit_time' => $at->format('H:i:s'),
                'technician_name' => $technicianName,
                'deposit_id' => $depositId,
                'is_for_workshop' => $isForWorkshop,
                'notes' => 'Consumo de stock para reposición',
                'created_by' => $this->operators[array_rand($this->operators)],
            ]);
            $this->backdate($exit, $at);

            $item = ProductExitItem::query()->create([
                'product_exit_id' => $exit->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
            $this->backdate($item, $at);

            $product->update(['available_quantity' => $target]);

            $productHistory = $history->recordExit(
                $product->fresh(),
                $exit->id,
                $quantity,
                $available,
                $target,
                $exit->technician_name,
                $exit->deposit_id,
            );
            $this->backdate($productHistory, $at);
        }
    }
}
