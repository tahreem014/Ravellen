<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\AwaitingStockOrderNotification;
use App\Notifications\ProductRestockedNotification;
use Illuminate\Support\Facades\Notification;

class ProductVariantObserver
{
    public function updated(ProductVariant $variant): void
    {
        $originalStatus = $variant->getOriginal('status');
        $isNowActive    = $variant->status === ProductVariant::STATUS_ACTIVE;
        $becameActive   = $isNowActive && $originalStatus !== ProductVariant::STATUS_ACTIVE;

        if (! $becameActive) {
            return;
        }

        // Both checks run independently — they target different audiences
        // (order owners waiting on this exact variant vs. wishlisters) and
        // a variant can go straight from any non-active status (out_of_stock,
        // restocking, pre_order, ...) to active, not just from one specific
        // prior status.
        $this->notifyAwaitingStockOrders($variant);
        $this->notifyRestock($variant);
    }

    /**
     * When a preorder variant comes back into stock, notify every customer
     * whose order is still waiting on it and move those orders into
     * payment_due so the remaining 60% can be collected.
     */
    private function notifyAwaitingStockOrders(ProductVariant $variant): void
    {
        Order::awaitingStock()
            ->whereHas('items', fn ($query) => $query->where('product_variant_id', $variant->id))
            ->with('user')
            ->get()
            ->each(function (Order $order) use ($variant) {
                if ($order->user) {
                    $order->user->notify(new AwaitingStockOrderNotification($order, $variant));
                }

                $order->update(['order_status' => Order::STATUS_PAYMENT_DUE]);
            });
    }

    /**
     * When a variant that was simply out of stock becomes available again,
     * notify users who wishlisted the product (general restock, unrelated
     * to the preorder/awaiting-stock flow).
     */
    private function notifyRestock(ProductVariant $variant): void
    {
        $product = $variant->product;

        if (! $product) {
            return;
        }

        $users = $product->wishLists()->with('user')->get()->pluck('user')->filter();

        if ($users->isNotEmpty()) {
            Notification::send($users, new ProductRestockedNotification($product));
        }
    }
}
