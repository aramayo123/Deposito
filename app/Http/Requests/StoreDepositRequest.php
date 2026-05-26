<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ignoreId = null;
        if ($this->route('deposit')) {
            $ignoreId = $this->route('deposit')->id ?? $this->route('deposit');
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('deposits', 'name')->ignore($ignoreId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del depósito es obligatorio.',
            'name.unique' => 'Ya existe un depósito con ese nombre.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
        ];
    }
}
