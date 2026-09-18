<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;

/**
 * Sent to users who wishlisted a product when it comes back into stock
 * after being out of stock (not the "awaiting stock order" preorder flow).
 */
class ProductRestockedNotification extends Notification
{
    public function __construct(private readonly Product $product)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'message'    => "{$this->product->name} has been restocked.",
            'action_url' => route('product-item', $this->product->slug),
        ];
    }
}
