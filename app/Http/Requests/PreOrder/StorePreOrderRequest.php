<?php

namespace App\Http\Requests\PreOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'         => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'qty'                => 'required|integer|min:1',
            'price'              => 'required|numeric|min:0',
            'advanced_payment'   => 'required|numeric|min:0',
            'remaining_payment'  => 'required|numeric|min:0',
            'payment_method'     => 'required|string|max:255',
            'payment_status'     => 'nullable|string|in:pending,paid,failed,refunded',
        ];
    }
}
