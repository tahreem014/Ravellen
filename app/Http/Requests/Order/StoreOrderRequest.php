<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => ['required', 'string', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'address_id'       => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(fn ($query) => $query->where('user_id', $this->user()?->id)),
            ],
            'cart_item_ids'    => ['required', 'array', 'min:1'],
            'cart_item_ids.*'  => ['integer', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'cart_item_ids.required' => 'Select at least one item from your cart to check out.',
            'cart_item_ids.min'      => 'Select at least one item from your cart to check out.',
            'shipping_phone.regex' => 'Phone number must start with + and country code (e.g. +923001234567).',
        ];
    }
}
