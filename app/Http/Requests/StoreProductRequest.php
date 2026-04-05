<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_code' => 'required|string|max:50|unique:products,product_code',
            'name' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'available_quantity' => 'required|integer|min:0',
            'damaged_quantity' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ];
    }
}
