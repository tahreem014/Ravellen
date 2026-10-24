<?php

namespace App\DataTransferObjects\Payment;

use App\Models\Order;

/**
 * Read-model describing a preorder's remaining-balance payment, for
 * display/response purposes. The amount always comes from the order
 * record — never from client input.
 */
final readonly class RemainingPaymentDTO
{
    public function __construct(
        public int    $orderId,
        public float  $remainingAmount,
        public string $currency,
        public bool   $isPayable,
    ) {}

    public static function fromOrder(Order $order): self
    {
        return new self(
            orderId:         $order->id,
            remainingAmount: (float) $order->remaining_amount,
            currency:        'PKR',
            isPayable:       $order->isAwaitingRemainingPayment(),
        );
    }

    public function toArray(): array
    {
        return [
            'order_id'         => $this->orderId,
            'remaining_amount' => $this->remainingAmount,
            'currency'         => $this->currency,
            'is_payable'       => $this->isPayable,
        ];
    }
}
