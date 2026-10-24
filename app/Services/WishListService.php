<?php

namespace App\Services;

use App\DataTransferObjects\WishList\UpdateWishListData;
use App\DataTransferObjects\WishList\WishListData;
use App\Models\User;
use App\Models\WishList;
use Illuminate\Support\Collection;

class WishListService
{
    public function listWishlist(User $user): Collection
    {
        return $user->wishLists()
            ->with(['product.variants', 'product.images'])
            ->latest()
            ->get()
            ->map(fn (WishList $wishlist) => WishListData::fromModel($wishlist));
    }

    public function updateWishlist(User $user, WishList $wishlist, UpdateWishListData $data): WishListData
    {
        $this->authorizeOwnership($user, $wishlist);

        $wishlist->update([
            'product_id' => $data->productId ?? $wishlist->product_id,
        ]);

        return WishListData::fromModel($wishlist->fresh(['product.variants', 'product.images']));
    }

    public function deleteWishlist(User $user, WishList $wishlist): void
    {
        $this->authorizeOwnership($user, $wishlist);

        $wishlist->delete();
    }

    /**
     * Add the product to the user's wishlist if absent, remove it if present.
     * Used by the heart-button toggle on shop/category/product pages.
     */
    public function toggleProduct(User $user, int $productId): bool
    {
        $existing = $user->wishLists()->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $user->wishLists()->create(['product_id' => $productId]);

        return true;
    }

    public function removeProduct(User $user, int $productId): void
    {
        $user->wishLists()->where('product_id', $productId)->delete();
    }

    /**
     * Shape the user's DB-backed wishlist the same way the session-based
     * array is shaped, so the wish-list page view needs no branching.
     */
    public function listForDisplay(User $user): array
    {
        return $user->wishLists()
            ->with(['product.variants', 'product.images'])
            ->latest()
            ->get()
            ->filter(fn (WishList $wishlist) => $wishlist->product !== null)
            ->mapWithKeys(function (WishList $wishlist) {
                $product  = $wishlist->product;
                $minPrice = $product->cheapestVariant()?->effectivePrice();
                $id       = 'p-' . $product->id;

                return [$id => [
                    'id'    => $id,
                    'name'  => $product->name,
                    'price' => $minPrice ? 'From PKR ' . number_format($minPrice) : 'PKR —',
                    'image' => $product->images->first()?->image_url ?? '',
                ]];
            })
            ->toArray();
    }

    private function authorizeOwnership(User $user, WishList $wishlist): void
    {
        abort_unless($wishlist->user_id === $user->id, 403);
    }
}
