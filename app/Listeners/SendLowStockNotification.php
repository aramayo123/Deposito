<?php

namespace App\Listeners;

use App\Events\LowStockEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LowStockEvent $event): void
    {
        // La difusión en tiempo real la resuelve ShouldBroadcast en el evento.
        // Aquí se podría extender con correo, SMS, etc.
    }
}
