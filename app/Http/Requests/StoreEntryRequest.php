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
            'items.*.quantity_received' => 'required|numeric|min:0.001',
            'items.*.quantity_damaged' => 'nullable|numeric|min:0',
            'items.*.damage_notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'entry_date.required' => 'La fecha de entrada es obligatoria.',
            'entry_date.date' => 'La fecha de entrada no es válida.',
            'entry_time.required' => 'La hora de entrada es obligatoria.',
            'items.required' => 'Debe agregar al menos un ítem.',
            'items.*.product_id.required' => 'Debe seleccionar un producto.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity_received.required' => 'La cantidad recibida es obligatoria.',
            'items.*.quantity_received.numeric' => 'La cantidad recibida debe ser un número.',
            'items.*.quantity_received.min' => 'La cantidad recibida debe ser mayor a 0.',
            'items.*.quantity_damaged.numeric' => 'La cantidad dañada debe ser un número.',
            'items.*.quantity_damaged.min' => 'La cantidad dañada no puede ser negativa.',
        ];
    }

    public function attributes(): array
    {
        return [
            'entry_date' => 'fecha',
            'entry_time' => 'hora',
            'notes' => 'notas',
            'items.*.product_id' => 'producto',
            'items.*.quantity_received' => 'cantidad recibida',
            'items.*.quantity_damaged' => 'cantidad dañada',
            'items.*.damage_notes' => 'notas de daño',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $item) {
                $qd = (float) ($item['quantity_damaged'] ?? 0);
                if ($qd > 0 && empty(trim((string) ($item['damage_notes'] ?? '')))) {
                    $validator->errors()->add(
                        "items.{$i}.damage_notes",
                        'Las notas de daño son obligatorias cuando hay unidades dañadas.'
                    );
                }
                $qr = (float) ($item['quantity_received'] ?? 0);
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
