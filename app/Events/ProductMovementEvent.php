<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductMovementEvent implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $movementType,
        public string $code,
        public string $title,
        public string $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('deposito-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'product-movement';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'movement',
            'movement_type' => $this->movementType,
            'code' => $this->code,
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
}
