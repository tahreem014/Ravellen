<?php

namespace App\DataTransferObjects\Order;

use App\Models\Order;

final readonly class OrderData
{
    public function __construct(
        public int     $id,
        public string  $uuid,
        public string  $trackingNumber,
        public string  $orderType,
        public float   $totalAmount,
        public ?float  $advanceAmount,
        public ?float  $remainingAmount,
        public string  $orderStatus,
        public ?string $paymentStage,
        public string  $paymentMethod,
        public string  $paymentStatus,
        public string  $orderDate,
    ) {}

    public static function fromModel(Order $order): self
    {
        return new self(
            id:              $order->id,
            uuid:            $order->uuid,
            trackingNumber:  $order->tracking_number ?? 'RVL-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            orderType:       $order->order_type,
            totalAmount:     (float) $order->total_amount,
            advanceAmount:   $order->advance_amount !== null ? (float) $order->advance_amount : null,
            remainingAmount: $order->remaining_amount !== null ? (float) $order->remaining_amount : null,
            orderStatus:     $order->order_status,
            paymentStage:    $order->payment_stage,
            paymentMethod:   $order->payment_method,
            paymentStatus:   $order->payment_status,
            orderDate:       $order->order_date?->format('d/m/Y') ?? now()->format('d/m/Y'),
        );
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'uuid'             => $this->uuid,
            'tracking_number'  => $this->trackingNumber,
            'order_type'       => $this->orderType,
            'total_amount'     => $this->totalAmount,
            'advance_amount'   => $this->advanceAmount,
            'remaining_amount' => $this->remainingAmount,
            'order_status'     => $this->orderStatus,
            'payment_stage'    => $this->paymentStage,
            'payment_method'   => $this->paymentMethod,
            'payment_status'   => $this->paymentStatus,
            'order_date'       => $this->orderDate,
            /* Amount actually charged today — the full total for a normal
               order, just the 40% advance for a preorder. */
            'charged_amount'   => $this->advanceAmount ?? $this->totalAmount,
        ];
    }
}
