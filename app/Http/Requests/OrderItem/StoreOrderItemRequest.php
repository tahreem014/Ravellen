<?php

namespace App\Http\Requests\OrderItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'           => 'required|exists:orders,id',
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'qty'                => 'required|integer|min:1',
            'price'              => 'required|numeric|min:0',
            'comment'            => 'nullable|string|max:1000',
            'preference'         => 'nullable|string|max:255',
        ];
    }
}
