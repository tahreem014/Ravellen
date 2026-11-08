<?php

namespace App\Services;

use App\Models\AddToCart;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keeps each variant's stock in step with the orders placed for it.
 *
 * An order holds its quantities out of the stock from the moment it is placed — paid or not, so two
 * shoppers can never both get the last unit — and gives them back if it is cancelled (the admin
 * moving it to Cancelled; moving it out of Cancelled takes them again). order_items.stock_reserved
 * remembers what each line holds, so a line can only ever give back what it took.
 *
 * Pre-orders are taken without stock and never hold any. Every change goes through a normal model
 * save, so the variant's status follows its stock (ProductVariant::syncStatusWithStock) and open
 * storefront and admin pages notice it (LiveUpdateService::bump).
 */
class StockService
{
    /**
     * Take the quantities of these order lines out of their variants' stock. Call it inside the
     * transaction that places the order: the variants are locked until it ends, and a throw rolls
     * everything back, so a rejected order takes nothing.
     *
     * @param  Collection<int, AddToCart>  $items  the normal (not pre-order) lines of one order
     * @throws \RuntimeException when a variant is gone, no longer on sale, or has less left than asked for
     */
    public function take(Collection $items): void
    {
        $wanted   = $items->groupBy('product_variant_id')->map(fn (Collection $lines) => (int) $lines->sum('qty'));
        $variants = ProductVariant::with('product')->whereIn('id', $wanted->keys())->orderBy('id')->lockForUpdate()->get()->keyBy('id');

        foreach ($wanted as $variantId => $qty) {
            $variant = $variants->get($variantId);

            if (! $variant || $variant->status !== ProductVariant::STATUS_ACTIVE) {
                throw new \RuntimeException(($variant ? $this->label($variant) : 'An item in your cart') . ' is no longer available. Please remove it from your cart and try again.');
            }

            $left = max(0, (int) $variant->stock);

            if ($left < $qty) {
                throw new \RuntimeException($left > 0
                    ? "Only {$left} left of {$this->label($variant)}. Please lower the quantity and try again."
                    : "{$this->label($variant)} just sold out. Please remove it from your cart and try again.");
            }
        }

        foreach ($wanted as $variantId => $qty) {
            $variant        = $variants->get($variantId);
            $variant->stock = (int) $variant->stock - $qty;
            $variant->save();
        }
    }

    /**
     * Give back what the order's lines are holding (it was cancelled). Safe to call twice. A variant
     * that had sold out has stock again, so it goes back on sale.
     */
    public function release(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lines = $order->items()->where('stock_reserved', '>', 0)->get();

            if ($lines->isEmpty()) {
                return;
            }

            $variants = $this->lock($lines->pluck('product_variant_id'));

            foreach ($lines as $line) {
                if ($variant = $variants->get($line->product_variant_id)) {
                    $soldOut = (int) $variant->stock <= 0 && $variant->status === ProductVariant::STATUS_OUT_OF_STOCK;

                    $variant->stock = (int) $variant->stock + (int) $line->stock_reserved;
                    if ($soldOut) {
                        $variant->status = ProductVariant::STATUS_ACTIVE;
                    }
                    $variant->save();
                }

                $line->update(['stock_reserved' => 0]);
            }
        });
    }

    /**
     * Take again what a cancelled order gave back (the admin un-cancelled it). Only lines that held
     * stock once are touched, and never more than what is left; a variant that is no longer on
     * sale is left alone.
     */
    public function retake(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $lines = $order->items()->where('stock_reserved', 0)->get();

            if ($lines->isEmpty()) {
                return;
            }

            $variants = $this->lock($lines->pluck('product_variant_id'));

            foreach ($lines as $line) {
                $variant = $variants->get($line->product_variant_id);

                if (! $variant || $variant->status !== ProductVariant::STATUS_ACTIVE) {
                    continue;
                }

                $taken = min((int) $line->qty, max(0, (int) $variant->stock));

                if ($taken > 0) {
                    $variant->stock = (int) $variant->stock - $taken;
                    $variant->save();
                    $line->update(['stock_reserved' => $taken]);
                }
            }
        });
    }

    /** @return Collection<int, ProductVariant> the variants, locked until the transaction ends, by id */
    private function lock(Collection $variantIds): Collection
    {
        return ProductVariant::whereIn('id', $variantIds->unique())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    private function label(ProductVariant $variant): string
    {
        $product = $variant->product?->name;

        return $product ? "{$product} ({$variant->variant_name})" : $variant->variant_name;
    }
}
