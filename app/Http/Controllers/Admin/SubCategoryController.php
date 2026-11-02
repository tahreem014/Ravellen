<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        // ?category=ID narrows the list to one category (the Categories tab links here)
        $filter = $categories->firstWhere('id', $request->integer('category'));

        $subCategories = SubCategory::query()
            ->select('sub_categories.*')
            ->join('categories', 'categories.id', '=', 'sub_categories.category_id')
            ->with('category')
            ->withCount('products')
            ->when($filter, fn ($query) => $query->where('sub_categories.category_id', $filter->id))
            ->orderBy('categories.name')
            ->orderBy('sub_categories.name')
            ->get();

        return view('admin.sub-categories.index', compact('categories', 'subCategories', 'filter'));
    }

    public function store(Request $request)
    {
        SubCategory::create($this->validateData($request));

        return back()->with('status', 'Sub-category added.');
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $data = $this->validateData($request, $subCategory);

        // A product's sub-category must belong to its category, so a sub-category with products stays put
        $moved = (int) $data['category_id'] !== $subCategory->category_id;

        if ($moved && $count = $subCategory->products()->count()) {
            return back()->with('error', "Can't move \"{$subCategory->name}\" to another category: {$count} "
                . ($count === 1 ? 'product uses' : 'products use') . ' it. Edit '
                . ($count === 1 ? 'that product' : 'those products') . ' to use another sub-category first.');
        }

        $subCategory->update($data);

        return back()->with('status', 'Sub-category updated.');
    }

    public function destroy(SubCategory $subCategory)
    {
        if ($count = $subCategory->products()->count()) {
            return back()->with('error', "Can't delete \"{$subCategory->name}\": {$count} " . ($count === 1 ? 'product uses' : 'products use')
                . ' it. Edit ' . ($count === 1 ? 'that product' : 'those products') . ' to use another sub-category first.');
        }

        $subCategory->delete();

        return back()->with('status', 'Sub-category deleted.');
    }

    private function validateData(Request $request, ?SubCategory $subCategory = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('sub_categories', 'name')
                    ->where('category_id', $request->input('category_id'))
                    ->ignore($subCategory?->id),
            ],
        ], [
            'category_id.required' => 'Choose the category this sub-category belongs to.',
            'name.unique' => 'That category already has a sub-category with that name.',
        ]);
    }
}
