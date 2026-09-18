<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        SubCategory::create($data);

        return back()->with('status', 'Sub-category added.');
    }

    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->products()->exists()) {
            return back()->withErrors(['sub_category' => 'Cannot delete a sub-category that still has products.']);
        }

        $subCategory->delete();

        return back()->with('status', 'Sub-category deleted.');
    }
}
