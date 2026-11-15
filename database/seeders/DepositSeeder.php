<?php

namespace Database\Seeders;

use App\Models\Deposit;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepositSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $start = now()->subMonths(6)->startOfDay();

        $names = [
            'Depósito Central',
            'Taller Mecánico',
            'Pañol Eléctrico',
            'Obra Norte',
            'Obra Sur',
            'Camioneta de Reparto',
            'Depósito '.Str::title(fake()->unique()->city()),
            'Depósito '.Str::title(fake()->unique()->city()),
        ];

        foreach ($names as $index => $name) {
            $deposit = Deposit::query()->firstOrCreate(['name' => $name]);

            $this->backdate(
                $deposit,
                $start->copy()->addDays($index)->addMinutes(fake()->numberBetween(0, 600)),
            );
        }
    }
}
