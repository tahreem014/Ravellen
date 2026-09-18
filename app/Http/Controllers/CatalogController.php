<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        $categories = Category::with([
            'subCategories.products.variants',
            'subCategories.products.images',
            'products' => fn ($query) => $query->whereNull('sub_category_id'),
            'products.variants',
            'products.images',
        ])->orderBy('name')->get();

        return view('pages.catalog-browser', ['categories' => $categories]);
    }
}
