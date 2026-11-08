<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Services\StockService;

class OrderObserver
{
    public function __construct(private readonly StockService $stock)
    {
    }

    /**
     * Notify the user whenever their order moves to a new status, and keep the stock right:
     * a cancelled order gives back the units it held, an un-cancelled one takes them again.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $wasCancelled = $order->getOriginal('order_status') === Order::STATUS_CANCELLED;
        $isCancelled  = $order->order_status === Order::STATUS_CANCELLED;

        if ($isCancelled && ! $wasCancelled) {
            $this->stock->release($order);
        } elseif ($wasCancelled && ! $isCancelled) {
            $this->stock->retake($order);
        }

        $order->user?->notify(new OrderStatusUpdatedNotification($order, $order->order_status));
    }
}
