<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductMediaService;
use App\Services\ProductPricingService;
use App\Services\SaleNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricingService,
        private readonly ProductMediaService $mediaService,
        private readonly SaleNotifier $saleNotifier,
    ) {
    }

    public function index(Request $request)
    {
        $products = Product::with(['category', 'subCategory', 'images', 'variants'])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::with('subCategories')->orderBy('name')->get();
        $pricing = $this->pricingService->formValues(null);
        $mediaLimits = $this->mediaService->limits();

        return view('admin.products.create', compact('categories', 'pricing', 'mediaLimits'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request, $this->mediaService->rules(required: false));

        $product = Product::create(collect($data)->only(['name', 'description', 'category_id', 'sub_category_id'])->all());
        $this->applyPricing($product, $data);
        $mediaCount = $this->mediaService->store($product, $request->file('media', []));

        $media = $mediaCount . ' ' . ($mediaCount === 1 ? 'photo/video' : 'photos/videos');

        return redirect()->route('admin.products.edit', $product)
            ->with('status', $product->usesPackPricing()
                ? 'Product created with its pack prices' . ($mediaCount ? " and {$media}" : '') . '.'
                : 'Product created' . ($mediaCount ? " with {$media}" : '') . '. Now add its variants below.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants', 'images', 'category', 'subCategory']);
        $categories = Category::with('subCategories')->orderBy('name')->get();
        $pricing = $this->pricingService->formValues($product);
        $mediaLimits = $this->mediaService->limits();

        return view('admin.products.edit', compact('product', 'categories', 'pricing', 'mediaLimits'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);

        $discountBefore = $this->saleNotifier->bestDiscount($product);
        $product->update(collect($data)->only(['name', 'description', 'category_id', 'sub_category_id'])->all());
        $this->applyPricing($product, $data);
        $this->saleNotifier->notifyIfIncreased($product, $discountBefore);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            foreach ([$image->image, $image->video] as $file) {
                if ($file && file_exists(public_path('images/products/' . $file))) {
                    unlink(public_path('images/products/' . $file));
                }
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validateProduct(Request $request, array $extraRules = []): array
    {
        return $request->validate($extraRules + [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'pricing_mode' => ['nullable', Rule::in([Product::PRICING_AUTOMATIC, Product::PRICING_CUSTOM])],

            // Automatic: one price for 5 balls; every bigger pack is a multiple of it.
            'base_price' => ['exclude_unless:pricing_mode,automatic', 'required', 'numeric', 'gt:0'],
            'base_sale_price' => ['exclude_unless:pricing_mode,automatic', 'nullable', 'numeric', 'gt:0', 'lt:base_price'],
            'max_qty' => ['exclude_unless:pricing_mode,automatic', 'required', 'integer', 'min:5', 'max:100', 'multiple_of:5'],

            // Custom: one row per quantity option.
            'packs' => ['exclude_unless:pricing_mode,custom', 'required', 'array', 'min:1'],
            'packs.*.qty' => ['exclude_unless:pricing_mode,custom', 'required', 'integer', 'min:5', 'max:100', 'multiple_of:5', 'distinct'],
            'packs.*.price' => ['exclude_unless:pricing_mode,custom', 'required', 'numeric', 'gt:0'],
            'packs.*.sale_price' => ['exclude_unless:pricing_mode,custom', 'nullable', 'numeric', 'gt:0', 'lt:packs.*.price'],
        ], $this->mediaService->messages() + [
            'base_price.required' => 'Enter the price for 5 balls.',
            'base_sale_price.lt' => 'The sale price must be lower than the regular price.',
            'packs.required' => 'Add at least one quantity with its price.',
            'packs.*.qty.multiple_of' => 'Quantities must be multiples of 5.',
            'packs.*.qty.distinct' => 'Each quantity can only be listed once.',
            'packs.*.price.required' => 'Enter a price for every quantity.',
            'packs.*.sale_price.lt' => "Each sale price must be lower than that quantity's regular price.",
        ]);
    }

    /**
     * Standard products keep whatever variants they have; Mystery Balls modes
     * rebuild the pack variants from the submitted pricing.
     */
    private function applyPricing(Product $product, array $data): void
    {
        $mode = $data['pricing_mode'] ?? null;

        if ($mode === Product::PRICING_AUTOMATIC) {
            $this->pricingService->applyAutomatic(
                $product,
                (float) $data['base_price'],
                isset($data['base_sale_price']) ? (float) $data['base_sale_price'] : null,
                (int) $data['max_qty'],
            );
        } elseif ($mode === Product::PRICING_CUSTOM) {
            $packs = collect($data['packs'])->mapWithKeys(fn (array $pack) => [(int) $pack['qty'] => [
                'price' => (float) $pack['price'],
                'sale_price' => isset($pack['sale_price']) ? (float) $pack['sale_price'] : null,
            ]])->all();

            $this->pricingService->applyCustom($product, $packs);
        } elseif ($product->pricing_mode !== null) {
            $product->update(['pricing_mode' => null]);
        }
    }
}
