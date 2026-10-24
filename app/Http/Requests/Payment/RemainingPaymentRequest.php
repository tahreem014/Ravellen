<?php

namespace App\Http\Requests\Payment;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorizes a remaining-balance payment initiation for one preorder.
 * The remaining amount is always read from the order record — the
 * customer can never submit it.
 */
class RemainingPaymentRequest extends FormRequest
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
        return [];
    }
}
