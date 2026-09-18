<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => 'required|exists:products,id',
            'variant_name' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'min_qty'      => 'required|integer|min:1',
            'max_qty'      => 'required|integer|gte:min_qty',
            'stock'        => 'required|integer|min:0',
            'status'       => 'required|string|in:active,inactive,draft',
        ];
    }
}
