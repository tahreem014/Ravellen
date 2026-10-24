<?php

namespace App\Services;

use App\DataTransferObjects\Cart\CartItemData;
use App\DataTransferObjects\Cart\StoreCartItemData;
use App\Models\AddToCart;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Return all cart items for the given user as DTOs.
     */
    public function getItems(User $user): Collection
    {
        return $user->cartItems()
            ->with(['product.images', 'product.category', 'productVariant'])
            ->get()
            ->map(fn (AddToCart $item) => CartItemData::fromModel($item));
    }

    /**
     * Add a new item or increment qty if same product+variant already exists.
     *
     * The variant is what carries the price, so it must be one of this
     * product's own variants — a row without one would check out at PKR 0.
     *
     * @throws \RuntimeException if the variant doesn't belong to the product, or is
     *         out_of_stock, restocking, or discontinued — only active and pre_order
     *         variants can be purchased.
     */
    public function addItem(User $user, StoreCartItemData $data): CartItemData
    {
        $variant = ProductVariant::find($data->productVariantId);

        if (! $variant || $variant->product_id !== $data->productId) {
            throw new \RuntimeException('Please choose an available option for this product.');
        }

        if (! $variant->isPurchasable()) {
            throw new \RuntimeException('This item is currently unavailable and cannot be added to your cart.');
        }

        $existing = $user->cartItems()
            ->where('product_id', $data->productId)
            ->where('product_variant_id', $data->productVariantId)
            ->first();

        if ($existing) {
            $existing->increment('qty', $data->qty);
            $existing->update([
                'comment'    => $data->comment    ?? $existing->comment,
                'preference' => $data->preference ?? $existing->preference,
            ]);
            $existing->refresh();
            $cartItem = $existing;
        } else {
            $cartItem = $user->cartItems()->create([
                'product_id'         => $data->productId,
                'product_variant_id' => $data->productVariantId,
                'qty'                => $data->qty,
                'comment'            => $data->comment,
                'preference'         => $data->preference,
            ]);
        }

        $cartItem->load(['product.images', 'product.category', 'productVariant']);

        return CartItemData::fromModel($cartItem);
    }

    /**
     * Update qty (and optionally comment/preference) for an existing cart item.
     */
    public function updateItem(User $user, int $cartItemId, int $qty, ?string $comment = null, ?string $preference = null): CartItemData
    {
        $cartItem = $user->cartItems()->findOrFail($cartItemId);

        $cartItem->update([
            'qty'        => max(1, $qty),
            'comment'    => $comment    ?? $cartItem->comment,
            'preference' => $preference ?? $cartItem->preference,
        ]);

        $cartItem->load(['product.images', 'product.category', 'productVariant']);

        return CartItemData::fromModel($cartItem);
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(User $user, int $cartItemId): void
    {
        $user->cartItems()->where('id', $cartItemId)->delete();
    }

    /**
     * Remove all items from the user's cart.
     */
    public function clearCart(User $user): void
    {
        $user->cartItems()->delete();
    }
}
