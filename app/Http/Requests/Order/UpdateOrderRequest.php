<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'          => 'sometimes|required|exists:users,id',
            'shipping_fee'     => 'sometimes|required|numeric|min:0',
            'shipping_address' => 'sometimes|required|string|max:255',
            'total_amount'     => 'sometimes|required|numeric|min:0',
            'order_date'       => 'nullable|date',
            'order_type'       => 'sometimes|required|string|in:normal,pre_order',
            'order_status'     => 'sometimes|required|string|in:pending,awaiting_stock,payment_due,confirmed,processing,shipped,delivered,cancelled',
            'payment_method'   => 'sometimes|required|string|max:255',
            'payment_status'   => 'sometimes|required|string|in:pending,paid,failed,refunded',
            'payment_stage'    => 'nullable|string|in:advance_paid,completed',
            'advance_amount'   => 'nullable|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'tracking_number'  => 'nullable|string|max:255',
            'delivered_at'     => 'nullable|date',
        ];
    }
}
