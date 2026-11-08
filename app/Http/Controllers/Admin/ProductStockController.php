<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    /**
     * GET /admin/products/stock?ids[]=…
     * Polled every few seconds by the product list and the edit page, so every order that takes
     * stock shows up without a refresh. Each product comes back as the markup of its stock cell
     * (the same partial the list renders), a signature that moves whenever any variant's stock or
     * status does, and the raw figures the edit page patches into its form. A product that no
     * longer exists is simply missing from the answer.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $ids = collect((array) $request->query('ids', []))
            ->map(fn ($id) => (int) $id)->filter()->unique()->take(100)->values();

        $products = Product::with('variants')->whereIn('id', $ids)->get()
            ->mapWithKeys(fn (Product $product) => [$product->id => [
                'total'     => $product->stockTotal(),
                'signature' => $product->stockSignature(),
                'html'      => view('admin.products.partials.stock-cell', compact('product'))->render(),
                'variants'  => $product->variants->mapWithKeys(fn (ProductVariant $variant) => [
                    $variant->id => ['stock' => (int) $variant->stock, 'status' => $variant->status],
                ])->all(),
            ]]);

        return response()->json(['products' => (object) $products->all()])->header('Cache-Control', 'no-store');
    }
}
