<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;

class OrderObserver
{
    /**
     * Notify the user whenever their order moves to a new status.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $order->user?->notify(new OrderStatusUpdatedNotification($order, $order->order_status));
    }
}
