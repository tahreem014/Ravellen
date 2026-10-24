<?php

namespace App\DataTransferObjects\Payment;

/**
 * Everything needed to open a Safepay checkout session for one order.
 * Always built server-side from the order record — amount is never
 * accepted from the client.
 */
final readonly class PaymentInitiationDTO
{
    public function __construct(
        public int    $orderId,
        public float  $amount,
        public string $currency,
        public string $orderReference,
        public string $successUrl,
        public string $cancelUrl,
    ) {}
}
