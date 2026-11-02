<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount(['products', 'subCategories'])
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        Category::create($this->validateData($request));

        return back()->with('status', 'Category added.');
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validateData($request, $category));

        return back()->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($count = $category->productCount()) {
            return back()->with('error', "Can't delete \"{$category->name}\": {$count} " . ($count === 1 ? 'product is' : 'products are')
                . ' filed under it. Edit ' . ($count === 1 ? 'that product' : 'those products') . ' to use another category first.');
        }

        $category->delete();   // its sub-categories go with it, and none of them has a product

        return back()->with('status', 'Category deleted.');
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category?->id)],
        ], [
            'name.unique' => 'There is already a category with that name.',
        ]);
    }
}
