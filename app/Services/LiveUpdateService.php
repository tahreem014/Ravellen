<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Keeps storefront pages in step with the admin without a refresh. Every
 * catalog change bumps a version number, and every change to a customer's
 * orders or refunds bumps that customer's own orders version. Open pages poll
 * GET /live with the versions they last saw and only fetch fresh data when one
 * moved (see layouts/live-updates.blade.php).
 */
class LiveUpdateService
{
    private const VERSION_KEY = 'storefront:version';

    public function version(): string
    {
        return Cache::rememberForever(self::VERSION_KEY, fn () => $this->newVersion());
    }

    /** Called on every product / variant / media / category save or delete (AppServiceProvider). */
    public function bump(): void
    {
        DB::afterCommit(fn () => Cache::forever(self::VERSION_KEY, $this->newVersion()));
    }

    public function ordersVersion(int $userId): string
    {
        return Cache::rememberForever($this->ordersKey($userId), fn () => $this->newVersion());
    }

    /** Called when one of this customer's orders or refund requests is saved or deleted (AppServiceProvider). */
    public function bumpOrders(?int $userId): void
    {
        if ($userId) {
            DB::afterCommit(fn () => Cache::forever($this->ordersKey($userId), $this->newVersion()));
        }
    }

    /**
     * Current state of the given products, keyed by id. Products that no
     * longer exist are simply missing from the result.
     *
     * @param int[] $ids
     */
    public function products(array $ids): array
    {
        return Product::with(['variants', 'images'])->whereIn('id', $ids)->get()
            ->mapWithKeys(fn (Product $product) => [$product->id => $this->productSnapshot($product)])
            ->all();
    }

    public function productSnapshot(Product $product): array
    {
        $cheapest = $product->cheapestVariant();

        return [
            'name'        => $product->name,
            'description' => $product->description,
            'image'       => $product->images->first()?->image_url,
            'from'        => $cheapest ? $this->price($cheapest) : null,
            'badge'       => $product->discountLabel(),
            'structure'   => $this->structure($product),
            'variants'    => $product->variants->mapWithKeys(fn (ProductVariant $variant) => [$variant->id => $this->price($variant) + [
                'percent'   => $variant->discountPercent(),
                'preorder'  => $variant->isPreOrder(),
                'available' => $variant->isPurchasable(),
            ]])->all(),
        ];
    }

    /**
     * Fingerprint of what a product page is built from (pricing mode, which
     * variants / packs exist). When it changes the page needs re-rendering,
     * not just re-pricing.
     */
    public function structure(Product $product): string
    {
        return md5(json_encode([
            $product->pricing_mode,
            $product->variants->sortBy('id')->map(fn (ProductVariant $variant) => [
                $variant->id,
                $variant->variant_name,
                $variant->status === ProductVariant::STATUS_DISCONTINUED,
            ])->values(),
        ]));
    }

    /**
     * "From" price per category (home page cards), keyed by category id.
     *
     * @param int[] $ids
     */
    public function categories(array $ids): array
    {
        return Product::with('variants')->whereIn('category_id', $ids)->get()
            ->groupBy('category_id')
            ->map(fn ($products) => ($cheapest = ProductVariant::cheapestOf($products->flatMap->variants)) ? $this->price($cheapest) : null)
            ->all();
    }

    /**
     * Fingerprint of which products a listing shows: "shop" (everything) or
     * "category:Name|Name". A changed value means products were added or
     * removed, so the listing reloads.
     */
    public function listingSignature(string $listing): ?string
    {
        $query = Product::query();

        if (str_starts_with($listing, 'category:')) {
            $names = explode('|', substr($listing, strlen('category:')));
            $query->whereHas('category', fn ($q) => $q->whereIn('name', $names));
        } elseif ($listing !== 'shop') {
            return null;
        }

        return md5($query->orderBy('id')->pluck('id')->implode(','));
    }

    private function price(ProductVariant $variant): array
    {
        return [
            'price'    => $variant->effectivePrice(),
            'original' => $variant->hasDiscount() ? (float) $variant->price : null,
        ];
    }

    private function ordersKey(int $userId): string
    {
        return "orders:version:{$userId}";
    }

    private function newVersion(): string
    {
        return str_replace('.', '', (string) microtime(true)) . random_int(100, 999);
    }
}
