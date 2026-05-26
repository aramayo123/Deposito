<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $id = $product instanceof \App\Models\Product ? $product->id : $product;

        return [
            'product_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'product_code')->ignore($id),
            ],
            'name' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'available_quantity' => 'required|numeric|min:0',
            'damaged_quantity' => 'nullable|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'product_code.required' => 'El código del producto es obligatorio.',
            'product_code.unique' => 'Ya existe un producto con ese código.',
            'product_code.max' => 'El código no puede superar los 50 caracteres.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, JPEG, PNG o WebP.',
            'photo.max' => 'La foto no puede superar los 2 MB.',
            'available_quantity.required' => 'La cantidad disponible es obligatoria.',
            'available_quantity.numeric' => 'La cantidad disponible debe ser un número.',
            'available_quantity.min' => 'La cantidad disponible no puede ser negativa.',
            'damaged_quantity.numeric' => 'La cantidad dañada debe ser un número.',
            'damaged_quantity.min' => 'La cantidad dañada no puede ser negativa.',
            'minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'minimum_stock.numeric' => 'El stock mínimo debe ser un número.',
            'minimum_stock.min' => 'El stock mínimo no puede ser negativo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'product_code' => 'código',
            'name' => 'nombre',
            'photo' => 'foto',
            'available_quantity' => 'cantidad disponible',
            'damaged_quantity' => 'cantidad dañada',
            'minimum_stock' => 'stock mínimo',
        ];
    }
}
