<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductMediaService;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductMediaService $mediaService)
    {
    }

    /** Save every photo / video picked in the edit page's Media section at once. */
    public function store(Request $request, Product $product)
    {
        $request->validate($this->mediaService->rules(required: true), $this->mediaService->messages());

        $count = $this->mediaService->store($product, $request->file('media', []));

        return redirect()->route('admin.products.edit', $product)
            ->with('status', $count === 1 ? '1 file uploaded.' : "{$count} files uploaded.");
    }

    public function destroy(Product $product, ProductImage $image)
    {
        foreach ([$image->image, $image->video] as $file) {
            if ($file && file_exists(public_path('images/products/' . $file))) {
                unlink(public_path('images/products/' . $file));
            }
        }

        $image->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Media removed.');
    }
}
