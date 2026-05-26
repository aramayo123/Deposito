<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductEntryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity_received' => (float) $this->quantity_received,
            'quantity_damaged' => (float) $this->quantity_damaged,
            'damage_notes' => $this->damage_notes,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
