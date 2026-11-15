<?php

namespace Database\Seeders;

use App\Services\NotificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos de demostración generados con Faker.
     * Todas las fechas se calculan al momento de ejecutar el seeder y quedan
     * dentro de los últimos 6 meses.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                UserSeeder::class,
                DepositSeeder::class,
                ProductSeeder::class,
                ProductHistorySeeder::class,
                ProductMovementSeeder::class,
                StockAlertSeeder::class,
            ]);
        });

        app(NotificationService::class)->clearDashboardCache();
    }
}
