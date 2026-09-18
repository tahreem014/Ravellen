<?php

namespace App\Http\Requests\RefundRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRefundRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_item_id' => 'sometimes|required|exists:order_items,id',
            'amount'        => 'sometimes|required|numeric|min:0',
            'bank_details'  => 'sometimes|required|string',
            'reason'        => 'sometimes|required|string',
            'status'        => 'nullable|string|in:pending,approved,rejected,cancelled,processed',
            'requested_at'  => 'nullable|date',
            'processed_at'  => 'nullable|date',
        ];
    }
}
