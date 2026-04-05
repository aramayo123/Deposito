<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_code' => $this->product_code,
            'name' => $this->name,
            'photo' => $this->photo ? asset('storage/'.$this->photo) : null,
            'available_quantity' => (int) $this->available_quantity,
            'damaged_quantity' => (int) $this->damaged_quantity,
            'minimum_stock' => (int) $this->minimum_stock,
            'is_active' => (bool) $this->is_active,
            'stock_status' => $this->stock_status,
            'total_quantity' => $this->total_quantity,
        ];
    }
}
