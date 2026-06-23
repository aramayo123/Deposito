<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exit_date' => 'required|date',
            'exit_time' => 'required',
            'technician_name' => 'required_if:is_for_workshop,false|nullable|string|max:255',
            'deposit_id' => 'nullable|exists:deposits,id',
            'is_for_workshop' => 'required|boolean',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'exit_date.required' => 'La fecha de salida es obligatoria.',
            'exit_date.date' => 'La fecha de salida no es válida.',
            'exit_time.required' => 'La hora de salida es obligatoria.',
            'technician_name.required_if' => 'El nombre del técnico es obligatorio cuando no es para el taller.',
            'technician_name.max' => 'El nombre del técnico no puede superar los 255 caracteres.',
            'deposit_id.exists' => 'El depósito seleccionado no existe.',
            'is_for_workshop.required' => 'Debe indicar si es para uso del taller.',
            'notes.max' => 'Las notas no pueden superar los 2000 caracteres.',
            'items.required' => 'Debe agregar al menos un ítem.',
            'items.*.product_id.required' => 'Debe seleccionar un producto.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.numeric' => 'La cantidad debe ser un número.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'exit_date' => 'fecha',
            'exit_time' => 'hora',
            'technician_name' => 'técnico',
            'deposit_id' => 'depósito',
            'is_for_workshop' => 'uso del taller',
            'notes' => 'notas',
            'items.*.product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->input('is_for_workshop');
        $workshop = in_array($v, [true, 1, '1', 'on', 'true'], true);
        $this->merge(['is_for_workshop' => $workshop]);
    }
}
