<?php

namespace App\Services;

use App\Models\AddToCart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductPricingService
{
    public const DEFAULT_MAX_QTY = 50;

    /**
     * Automatic mode: each pack costs the 5-ball price times the number of
     * 5-ball steps in it (10 balls = 2 × base, 15 balls = 3 × base …). The
     * prices are worked out here, never taken from the browser.
     */
    public function applyAutomatic(Product $product, float $basePrice, ?float $baseSalePrice, int $maxQty): void
    {
        $packs = [];

        for ($qty = Product::PACK_STEP; $qty <= $maxQty; $qty += Product::PACK_STEP) {
            $steps = $qty / Product::PACK_STEP;

            $packs[$qty] = [
                'price'      => round($basePrice * $steps, 2),
                'sale_price' => $baseSalePrice !== null ? round($baseSalePrice * $steps, 2) : null,
            ];
        }

        $this->syncPacks($product, Product::PRICING_AUTOMATIC, $packs);
    }

    /**
     * Custom mode: the admin priced every quantity option themselves.
     *
     * @param array<int, array{price: float, sale_price: ?float}> $packs keyed by ball count
     */
    public function applyCustom(Product $product, array $packs): void
    {
        ksort($packs);

        $this->syncPacks($product, Product::PRICING_CUSTOM, $packs);
    }

    /**
     * Values the product form starts from. Each mode gets sensible defaults
     * from the current packs, so switching modes in the form doesn't start
     * from a blank slate.
     */
    public function formValues(?Product $product): array
    {
        $packs = $product ? $product->packVariants() : collect();
        $first = $packs->first();
        $steps = $first ? $first->packSize() / Product::PACK_STEP : 1;

        return [
            'base_price'      => $first ? round((float) $first->price / $steps, 2) : null,
            'base_sale_price' => $first?->hasDiscount() ? round((float) $first->sale_price / $steps, 2) : null,
            'max_qty'         => $packs->last()?->packSize() ?? self::DEFAULT_MAX_QTY,
            'packs'           => $packs->isNotEmpty()
                ? $packs->map(fn (ProductVariant $variant) => [
                    'qty'        => $variant->packSize(),
                    'price'      => (float) $variant->price,
                    'sale_price' => $variant->hasDiscount() ? (float) $variant->sale_price : null,
                ])->all()
                : array_map(
                    fn (int $qty) => ['qty' => $qty, 'price' => null, 'sale_price' => null],
                    range(Product::PACK_STEP, self::DEFAULT_MAX_QTY, Product::PACK_STEP)
                ),
        ];
    }

    /**
     * Make the product's variants match $packs exactly: existing packs are
     * repriced (keeping their stock and status), missing ones are created,
     * and every other variant is retired.
     */
    private function syncPacks(Product $product, string $mode, array $packs): void
    {
        DB::transaction(function () use ($product, $mode, $packs) {
            $product->update(['pricing_mode' => $mode]);

            $variants = $product->variants()->get();
            $template = $variants->first(fn (ProductVariant $variant) => $variant->packSize() !== null);
            $keptIds  = [];

            foreach ($packs as $qty => $pack) {
                $variant = $variants->first(fn (ProductVariant $variant) => $variant->packSize() === $qty);

                if (! $variant) {
                    $product->variants()->create([
                        'variant_name' => ProductVariant::packName($qty),
                        'price'        => $pack['price'],
                        'sale_price'   => $pack['sale_price'],
                        'min_qty'      => 1,
                        'max_qty'      => $template?->max_qty ?? 10,
                        'stock'        => $template?->stock ?? 0,
                        'status'       => ProductVariant::STATUS_ACTIVE,
                    ]);
                    continue;
                }

                $keptIds[] = $variant->id;
                $variant->fill(['price' => $pack['price'], 'sale_price' => $pack['sale_price']]);

                // A quantity the admin removed earlier and has now added back.
                // Saved quietly: it isn't a restock, so no restock notifications.
                if ($variant->status === ProductVariant::STATUS_DISCONTINUED) {
                    $variant->status = ProductVariant::STATUS_ACTIVE;
                    $variant->saveQuietly();
                } else {
                    $variant->save();
                }
            }

            $variants
                ->reject(fn (ProductVariant $variant) => in_array($variant->id, $keptIds, true))
                ->each(fn (ProductVariant $variant) => $this->retire($variant));
        });
    }

    /**
     * Take a variant off sale. Reviews cascade-delete with their variant and
     * orders and carts would lose their price link, so a variant anything
     * points at is discontinued instead of deleted.
     */
    private function retire(ProductVariant $variant): void
    {
        $inUse = $variant->orderItems()->exists()
            || $variant->reviews()->exists()
            || AddToCart::where('product_variant_id', $variant->id)->exists();

        if (! $inUse) {
            $variant->delete();

            return;
        }

        if ($variant->status !== ProductVariant::STATUS_DISCONTINUED) {
            $variant->update(['status' => ProductVariant::STATUS_DISCONTINUED]);
        }
    }
}
