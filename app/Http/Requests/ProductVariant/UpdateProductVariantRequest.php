<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'   => 'sometimes|required|exists:products,id',
            'variant_name' => 'sometimes|required|string|max:255',
            'price'        => 'sometimes|required|numeric|min:0',
            'min_qty'      => 'sometimes|required|integer|min:1',
            'max_qty'      => 'sometimes|required|integer|gte:min_qty',
            'stock'        => 'sometimes|required|integer|min:0',
            'status'       => 'sometimes|required|string|in:active,inactive,draft',
        ];
    }
}
