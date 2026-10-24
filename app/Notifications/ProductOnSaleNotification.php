<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;

/**
 * Sent to every customer when the admin puts a product on sale (or makes its
 * discount bigger).
 */
class ProductOnSaleNotification extends Notification
{
    /**
     * @param bool $upTo the percentage applies to some variants only
     */
    public function __construct(
        private readonly Product $product,
        private readonly int $percent,
        private readonly bool $upTo,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $discount = ($this->upTo ? 'up to ' : '') . "{$this->percent}%";

        return [
            'product_id' => $this->product->id,
            'message'    => "Sale: {$this->product->name} is now {$discount} off!",
            'action_url' => route('product-item', $this->product->slug),
        ];
    }
}
