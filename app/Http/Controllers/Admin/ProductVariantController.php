<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
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

    public function store(Request $request, Product $product)
    {
        $data = $this->validateVariant($request);

        $product->variants()->create($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant added.');
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $data = $this->validateVariant($request);

        $variant->update($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant updated.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant removed.');
    }

    private function validateVariant(Request $request): array
    {
        return $request->validate([
            'variant_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_qty' => ['required', 'integer', 'min:1'],
            'max_qty' => ['required', 'integer', 'gte:min_qty'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);
    }
}
