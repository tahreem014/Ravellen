<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;

/**
 * Broadcast to every user when the admin publishes a new product.
 */
class NewProductNotification extends Notification
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
            'message'    => "New Product Added: {$this->product->name} is now available.",
            'action_url' => route('product-item', $this->product->slug),
        ];
    }
}
