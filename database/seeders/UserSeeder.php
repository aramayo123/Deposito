<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use SeedsDemoData;

    /**
     * Usuarios fijos de prueba (se muestran en /login) + usuarios faker.
     */
    public const TEST_USERS = [
        [
            'name' => 'Administrador',
            'email' => 'admin@deposito.com',
            'password' => 'admin123',
        ],
        [
            'name' => 'Operador de Depósito',
            'email' => 'operador@deposito.com',
            'password' => 'operador123',
        ],
    ];

    public function run(): void
    {
        $start = now()->subMonths(6)->startOfDay();

        foreach (self::TEST_USERS as $index => $testUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $testUser['email']],
                [
                    'name' => $testUser['name'],
                    'password' => Hash::make($testUser['password']),
                ],
            );

            $user->forceFill([
                'email_verified_at' => $start->copy()->addDays($index),
            ])->save();

            $this->backdate($user, $start->copy()->addDays($index));
        }

        for ($i = 0; $i < 12; $i++) {
            $createdAt = $this->randomDate($start, now());

            $user = User::factory()->create([
                'email_verified_at' => $createdAt,
            ]);

            $this->backdate($user, $createdAt);
        }
    }
}
