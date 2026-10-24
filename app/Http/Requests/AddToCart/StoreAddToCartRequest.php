<?php

namespace App\Http\Requests\AddToCart;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id'         => 'required|integer|exists:products,id',
            'product_variant_id' => 'required|integer|exists:product_variants,id',
            'qty'                => 'required|integer|min:1|max:100',
            'comment'            => 'nullable|string|max:1000',
            'preference'         => 'nullable|string|max:255',
        ];
    }
}
