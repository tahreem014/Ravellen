<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user whose order was waiting on a product marked "Awaiting
 * Stock" once that product's variant is restocked.
 */
class AwaitingStockOrderNotification extends Notification
{
    public function __construct(
        private readonly Order $order,
        private readonly ProductVariant $variant
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'order_id'         => $this->order->id,
            'product_id'       => $this->variant->product_id,
            'tracking_number'  => $this->order->tracking_number,
            'remaining_amount' => (float) $this->order->remaining_amount,
            'message'          => "Your pre-order product '{$this->variant->product->name}' is now available. Please complete your remaining payment.",
            'action_url'       => route('orders.show', $this->order),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your pre-order is now available — Ravellen')
            ->greeting('Good news, ' . $notifiable->name . '!')
            ->line("Your pre-order product '{$this->variant->product->name}' (tracking number {$this->order->tracking_number}) is now available.")
            ->line('Please complete the remaining payment of PKR ' . number_format((float) $this->order->remaining_amount, 2) . ' so we can ship it.')
            ->action('Pay Remaining Balance', route('orders.show', $this->order))
            ->line('Thanks for your patience!');
    }
}
