<?php

namespace App\Http\Requests\PreOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'         => 'sometimes|required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'qty'                => 'sometimes|required|integer|min:1',
            'price'              => 'sometimes|required|numeric|min:0',
            'advanced_payment'   => 'sometimes|required|numeric|min:0',
            'remaining_payment'  => 'sometimes|required|numeric|min:0',
            'payment_method'     => 'sometimes|required|string|max:255',
            'payment_status'     => 'sometimes|required|string|in:pending,paid,failed,refunded',
        ];
    }
}
