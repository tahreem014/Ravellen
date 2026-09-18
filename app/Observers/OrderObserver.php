<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;

class OrderObserver
{
    /**
     * Confirm the order the moment it's placed.
     */
    public function created(Order $order): void
    {
        $order->user?->notify(new OrderStatusUpdatedNotification($order, 'confirmed'));
    }

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
