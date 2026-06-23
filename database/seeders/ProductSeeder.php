<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['TAL-001', 'Taladro percutor 750W'],
            ['AMO-120', 'Amoladora angular 125mm'],
            ['HID-500', 'Hidrolavadora 1600W'],
            ['MECH-SET', 'Juego mechas HSS'],
            ['TOR-M6', 'Tornillos M6 x 25 caja'],
            ['CIN-10', 'Cinta métrica 10m'],
            ['LLA-SET', 'Juego llaves combinadas'],
            ['DIS-115', 'Disco corte 115mm'],
        ];

        for ($i = 0; $i < 50; $i++) {
            $t = $tools[$i % count($tools)];
            $code = $t[0].'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);
            $name = $i % 7 === 0 ? null : $t[1].' #'.($i + 1);
            $avail = match (true) {
                $i % 11 === 0 => 0,
                $i % 9 === 0 => 3,
                $i % 5 === 0 => 12,
                default => 20 + ($i * 2) % 40,
            };
            $min = 5 + ($i % 8);
            $damaged = ($i % 6 === 0) ? (1 + ($i % 4)) : 0;

            Product::query()->firstOrCreate(
                ['product_code' => $code],
                [
                    'name' => $name,
                    'photo' => null,
                    'available_quantity' => $avail,
                    'damaged_quantity' => $damaged,
                    'minimum_stock' => $min,
                    'is_active' => true,
                ]
            );
        }
    }
}
