<?php

namespace App\Http\Requests\RefundRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_item_id' => 'required|exists:order_items,id',
            'amount'        => 'required|numeric|min:0',
            'bank_details'  => 'required|string',
            'reason'        => 'required|string',
            'status'        => 'nullable|string|in:pending,approved,rejected,cancelled,processed',
            'requested_at'  => 'nullable|date',
            'processed_at'  => 'nullable|date',
        ];
    }
}
