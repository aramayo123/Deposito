<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Product $product,
        public string $alertType,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('deposito-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'low-stock';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'low_stock',
            'alert_type' => $this->alertType,
            'product_id' => $this->product->id,
            'product_code' => $this->product->product_code,
            'name' => $this->product->name,
            'available_quantity' => $this->product->available_quantity,
            'minimum_stock' => $this->product->minimum_stock,
            'title' => 'Alerta de stock',
            'message' => sprintf(
                '%s (%s): %s unidades (mínimo %s)',
                $this->product->product_code,
                $this->product->name ?? '—',
                rtrim(rtrim(number_format((float) $this->product->available_quantity, 3, '.', ''), '0'), '.'),
                rtrim(rtrim(number_format((float) $this->product->minimum_stock, 3, '.', ''), '0'), '.'),
            ),
        ];
    }
}
