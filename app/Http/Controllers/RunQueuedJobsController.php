<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Procesa trabajos en cola vía HTTP (cron con wget/curl en hosting compartido).
 * Requiere QUEUE_CRON_TOKEN en .env y ?token= en la URL.
 */
class RunQueuedJobsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $expected = (string) config('app.queue_cron_token', '');
        if ($expected === '') {
            abort(404);
        }

        $given = (string) $request->query('token', '');
        if (! hash_equals($expected, $given)) {
            abort(403, 'Forbidden');
        }

        $maxSeconds = max(5, min(120, (int) config('app.queue_cron_max_seconds', 55)));

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => $maxSeconds,
        ]);

        $out = trim(Artisan::output());

        return response($out !== '' ? $out : 'Cola vacía o procesada (sin salida).', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
