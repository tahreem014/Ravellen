{{--
    Category and sub-category dropdowns for the product form. Both lists come from the
    database (managed under Categories): choosing a category offers only its own sub-categories.
    Expects $categories (with subCategories); optionally $categoryId / $subCategoryId, the saved
    values, which a rejected submission's old input takes over from.
--}}
@php
    $selectedCategory    = old('category_id', $categoryId ?? null);
    $selectedSubCategory = old('sub_category_id', $subCategoryId ?? null);
    $subCategoriesByCategory = $categories->mapWithKeys(fn ($category) => [
        $category->id => $category->subCategories->sortBy('name')
            ->map(fn ($sub) => ['id' => $sub->id, 'name' => $sub->name])->values(),
    ]);
@endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="categorySelect">Category</label>
        <select name="category_id" id="categorySelect" class="form-select" required>
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label" for="subCategorySelect">Sub-category (optional)</label>
        <select name="sub_category_id" id="subCategorySelect" class="form-select">
            <option value="">None</option>
        </select>
    </div>
</div>
<script>
    (function () {
        const subCategoriesByCategory = @json($subCategoriesByCategory);
        const wanted = @json($selectedSubCategory);
        const categorySelect = document.getElementById('categorySelect');
        const subCategorySelect = document.getElementById('subCategorySelect');

        function populateSubCategories() {
            subCategorySelect.length = 0;
            subCategorySelect.add(new Option('None', ''));
            (subCategoriesByCategory[categorySelect.value] || []).forEach((sub) => {
                subCategorySelect.add(new Option(sub.name, sub.id, false, String(sub.id) === String(wanted)));
            });
        }

        categorySelect.addEventListener('change', populateSubCategories);
        populateSubCategories();
    })();
</script>
