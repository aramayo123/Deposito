<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => 'required|date',
            'entry_time' => 'required',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.quantity_damaged' => 'nullable|integer|min:0',
            'items.*.damage_notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $item) {
                $qd = (int) ($item['quantity_damaged'] ?? 0);
                if ($qd > 0 && empty(trim((string) ($item['damage_notes'] ?? '')))) {
                    $validator->errors()->add(
                        "items.{$i}.damage_notes",
                        'Las notas de daño son obligatorias cuando hay unidades dañadas.'
                    );
                }
                $qr = (int) ($item['quantity_received'] ?? 0);
                if ($qd > $qr) {
                    $validator->errors()->add(
                        "items.{$i}.quantity_damaged",
                        'La cantidad dañada no puede superar la recibida.'
                    );
                }
            }
        });
    }
}
