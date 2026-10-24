<?php

namespace App\Http\Requests\Payment;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorizes a payment initiation for one order. Carries no trusted input
 * at all — amount and payment state are always derived server-side from
 * the order record, never from the request.
 */
class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user() !== null
            && $order->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            // Purely a UX convenience for chaining a mixed-cart checkout
            // (normal + preorder) into a second payment — never trusted
            // for authorization or amount; re-validated when actually used.
            // A uuid (not the numeric id) and deliberately no `exists` rule,
            // which would let a caller probe which orders exist.
            'next_order_uuid' => ['nullable', 'uuid'],
        ];
    }
}
