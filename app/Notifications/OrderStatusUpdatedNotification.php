<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user whenever their order's status changes (including the
 * initial confirmation right after checkout).
 */
class OrderStatusUpdatedNotification extends Notification
{
    private const MESSAGES = [
        'confirmed'                 => 'Your order has been confirmed.',
        Order::STATUS_PROCESSING     => 'Your order is being prepared.',
        Order::STATUS_SHIPPED        => 'Your order has been shipped.',
        Order::STATUS_DELIVERED      => 'Your order has been delivered.',
        Order::STATUS_CANCELLED      => 'Your order has been cancelled.',
        Order::STATUS_AWAITING_STOCK => 'Your order is awaiting stock and will ship once the item is back in stock.',
        Order::STATUS_PAYMENT_DUE    => 'Your order needs the remaining payment before it can be prepared for shipping.',
    ];

    public function __construct(
        private readonly Order $order,
        private readonly string $status
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'order_id'        => $this->order->id,
            'tracking_number' => $this->order->tracking_number,
            'status'          => $this->status,
            'message'         => self::MESSAGES[$this->status] ?? "Your order status has been updated to {$this->status}.",
            'action_url'      => route('orders.show', $this->order),
        ];
    }
}
