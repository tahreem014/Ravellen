<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\ProductOnSaleNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Tells every customer when a product goes on sale. Admin actions take a
 * snapshot of the product's best discount before they change prices and
 * hand it back afterwards; customers are notified only when that discount
 * appeared or grew — never for a smaller discount or an ended sale.
 */
class SaleNotifier
{
    /** The product's best discount right now, in whole percent (0 = not on sale). */
    public function bestDiscount(Product $product): int
    {
        return (int) $product->variants()->get()
            ->reject(fn (ProductVariant $variant) => $variant->status === ProductVariant::STATUS_DISCONTINUED)
            ->map(fn (ProductVariant $variant) => $variant->discountPercent())
            ->max();
    }

    public function notifyIfIncreased(Product $product, int $before): void
    {
        $product->load('variants');
        $after = $this->bestDiscount($product);

        if ($after <= $before) {
            return;
        }

        $notification = new ProductOnSaleNotification($product, $after, str_starts_with((string) $product->discountLabel(), 'Up to'));

        User::query()->where('is_admin', false)->chunkById(200, function ($users) use ($notification) {
            Notification::send($users, $notification);
        });
    }
}
