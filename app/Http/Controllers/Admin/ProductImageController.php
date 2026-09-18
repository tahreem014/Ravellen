<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'video' => ['nullable', 'mimetypes:video/mp4,video/quicktime', 'max:20480'],
        ]);

        if (! $request->hasFile('image') && ! $request->hasFile('video')) {
            return back()->withErrors(['image' => 'Choose an image or a video to upload.']);
        }

        $destination = public_path('images/products');

        $imageName = null;
        $videoName = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destination, $imageName);
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $videoName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destination, $videoName);
        }

        $product->images()->create([
            'image' => $imageName,
            'video' => $videoName,
        ]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Media uploaded.');
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
