<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'license_plate' => 'nullable|string|max:20',
            'is_for_workshop' => 'required|boolean',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->input('is_for_workshop');
        $workshop = in_array($v, [true, 1, '1', 'on', 'true'], true);
        $this->merge(['is_for_workshop' => $workshop]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            foreach ($items as $i => $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);
                if ($productId < 1 || $qty < 1) {
                    continue;
                }
                $product = Product::query()->select(['id', 'product_code', 'available_quantity'])->find($productId);
                if (! $product) {
                    continue;
                }
                if ($qty > (int) $product->available_quantity) {
                    $validator->errors()->add(
                        "items.{$i}.quantity",
                        'Stock insuficiente para '.($product->product_code ?? 'producto #'.$productId)
                    );
                }
            }
        });
    }
}
