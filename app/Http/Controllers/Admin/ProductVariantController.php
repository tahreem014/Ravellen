<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\SaleNotifier;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    private const STATUSES = [
        ProductVariant::STATUS_ACTIVE,
        ProductVariant::STATUS_OUT_OF_STOCK,
        ProductVariant::STATUS_PRE_ORDER,
        ProductVariant::STATUS_RESTOCKING,
        ProductVariant::STATUS_DISCONTINUED,
    ];

    public function __construct(private readonly SaleNotifier $saleNotifier)
    {
    }

    public function store(Request $request, Product $product)
    {
        // Mystery Balls packs are created from the product's pricing settings.
        if ($product->usesPackPricing()) {
            return redirect()->route('admin.products.edit', $product)
                ->withErrors(['variant_name' => 'Add or remove quantities in the Pricing section above.']);
        }

        $data = $this->validateVariant($request);

        $discountBefore = $this->saleNotifier->bestDiscount($product);
        $product->variants()->create($data);
        $this->saleNotifier->notifyIfIncreased($product, $discountBefore);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant added.');
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        // A pack's name and prices come from the pricing settings, so only
        // its stock and availability are edited here.
        $data = $product->usesPackPricing()
            ? $request->validate([
                'stock' => ['required', 'integer', 'min:0'],
                'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            ])
            : $this->validateVariant($request);

        $discountBefore = $this->saleNotifier->bestDiscount($product);
        $variant->update($data);
        $this->saleNotifier->notifyIfIncreased($product, $discountBefore);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant updated.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant removed.');
    }

    /**
     * Put the whole product on sale (or end the sale): sets every variant's
     * sale price to the given percentage off its regular price, rounded to
     * whole rupees. An empty percentage clears all sale prices. Used by the
     * Discount box on the edit page and the "Set discount" dialog on the
     * product list, so it returns to whichever page it came from.
     */
    public function sale(Request $request, Product $product)
    {
        $data = $request->validate([
            'percent' => ['nullable', 'numeric', 'gt:0', 'lt:100'],
        ]);

        $percent = isset($data['percent']) ? (float) $data['percent'] : null;
        $discountBefore = $this->saleNotifier->bestDiscount($product);

        foreach ($product->variants as $variant) {
            $variant->update(['sale_price' => $percent ? $this->discountedPrice($product, $variant, $percent) : null]);
        }

        $this->saleNotifier->notifyIfIncreased($product, $discountBefore);

        $message = $percent
            ? rtrim(rtrim(number_format($percent, 2), '0'), '.') . "% off applied to {$product->name}."
            : "Discount removed from {$product->name}.";

        // The product list's "Set discount" dialog saves in the background and updates its row in place.
        if ($request->expectsJson()) {
            $product->load('variants');
            $prices = $product->variants->map(fn (ProductVariant $variant) => $variant->effectivePrice());

            return response()->json([
                'message'   => $message,
                'discount'  => $product->discountLabel(),
                'percent'   => $product->variants->map(fn (ProductVariant $variant) => $variant->discountPercent())->filter()->max(),
                'price_min' => $prices->min(),
                'price_max' => $prices->max(),
            ]);
        }

        return back()->with('status', $message);
    }

    private function discountedPrice(Product $product, ProductVariant $variant, float $percent): float
    {
        // Automatic Mystery Balls packs stay exact multiples of the 5-ball
        // sale price, the same way the pricing form builds them.
        $steps = $product->pricing_mode === Product::PRICING_AUTOMATIC && $variant->packSize()
            ? $variant->packSize() / Product::PACK_STEP
            : 1;

        return max(1, round((float) $variant->price / $steps * (1 - $percent / 100))) * $steps;
    }

    private function validateVariant(Request $request): array
    {
        return $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'gt:0', 'lt:price'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['required', 'integer', 'gte:min_qty'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ], [
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
        ]);
    }
}
