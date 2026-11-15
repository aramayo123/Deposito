<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Product;
use App\Models\ProductEntry;
use App\Models\ProductEntryItem;
use App\Models\ProductExit;
use App\Models\ProductExitItem;
use App\Models\ProductHistory;
use App\Models\StockAlert;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Regenera por completo la base de datos con datos de demostración.
 * Sin token solo se permite desde localhost en local/testing; en cualquier
 * otro caso requiere SEEDERS_TOKEN y ?token=... para evitar borrados accidentales.
 */
class RunSeedersController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $expected = (string) config('app.seeders_token', '');
        $isLocalEnvironment = app()->environment('local') || app()->environment('testing');
        $isLocalRequest = in_array($request->ip(), ['127.0.0.1', '::1'], true);

        if ($expected === '' && (! $isLocalEnvironment || ! $isLocalRequest)) {
            abort(404);
        }

        if ($expected !== '' && ! hash_equals($expected, (string) $request->query('token', ''))) {
            abort(403, 'Forbidden');
        }

        @set_time_limit(300);

        try {
            $exitCode = Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            return response(
                "Error al ejecutar las migraciones y seeders:\n".$e->getMessage(),
                500,
            )->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        app(NotificationService::class)->clearDashboardCache();

        $firstHistory = ProductHistory::query()->min('created_at');
        $lastHistory = ProductHistory::query()->max('created_at');

        $summary = implode("\n", [
            'Seeders ejecutados correctamente.',
            'Usuarios: '.User::query()->count(),
            'Depósitos: '.Deposit::query()->count(),
            'Productos: '.Product::query()->count(),
            'Entradas: '.ProductEntry::query()->count(),
            'Ítems de entrada: '.ProductEntryItem::query()->count(),
            'Salidas: '.ProductExit::query()->count(),
            'Ítems de salida: '.ProductExitItem::query()->count(),
            'Historiales: '.ProductHistory::query()->count(),
            'Alertas: '.StockAlert::query()->count(),
            'Rango del historial: '.($firstHistory ?? '-').' a '.($lastHistory ?? '-'),
        ]);

        return response(
            $summary."\n\n".$output,
            $exitCode === 0 ? 200 : 500,
        )->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
