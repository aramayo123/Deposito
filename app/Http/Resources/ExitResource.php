<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exit_code' => $this->exit_code,
            'exit_date' => $this->exit_date?->format('Y-m-d'),
            'exit_time' => $this->exit_time,
            'technician_name' => $this->technician_name,
            'deposit_id' => $this->deposit_id,
            'deposit' => $this->whenLoaded('deposit', fn () => [
                'id' => $this->deposit->id,
                'name' => $this->deposit->name,
            ]),
            'is_for_workshop' => (bool) $this->is_for_workshop,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'items_count' => $this->whenCounted('items'),
            'items' => ProductExitItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
