<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entry_code' => $this->entry_code,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'entry_time' => $this->entry_time,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'items_count' => $this->whenCounted('items'),
            'items' => ProductEntryItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
