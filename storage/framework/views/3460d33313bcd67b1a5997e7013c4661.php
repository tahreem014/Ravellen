
<?php
    $selectedCategory    = old('category_id', $categoryId ?? null);
    $selectedSubCategory = old('sub_category_id', $subCategoryId ?? null);
    $subCategoriesByCategory = $categories->mapWithKeys(fn ($category) => [
        $category->id => $category->subCategories->sortBy('name')
            ->map(fn ($sub) => ['id' => $sub->id, 'name' => $sub->name])->values(),
    ]);
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="categorySelect">Category</label>
        <select name="category_id" id="categorySelect" class="form-select" required>
            <option value="">Select category</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>" <?php if((string) $selectedCategory === (string) $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
        const subCategoriesByCategory = <?php echo json_encode($subCategoriesByCategory, 15, 512) ?>;
        const wanted = <?php echo json_encode($selectedSubCategory, 15, 512) ?>;
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
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/partials/category-fields.blade.php ENDPATH**/ ?>