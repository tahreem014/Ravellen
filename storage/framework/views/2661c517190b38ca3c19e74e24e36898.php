<?php $__env->startSection('title', 'Sub-categories'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" action="<?php echo e(route('admin.sub-categories.index')); ?>" class="admin-toolbar-form">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Show sub-categories of">
                <option value="">All categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php if($filter?->id === $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($filter): ?>
                <a href="<?php echo e(route('admin.sub-categories.index')); ?>" class="btn btn-sm btn-link">Clear</a>
            <?php endif; ?>
        </form>
        <?php if($categories->isNotEmpty()): ?>
            <button type="button" class="btn btn-admin-primary btn-sm admin-toolbar-btn" data-add-sub-category data-category-id="<?php echo e($filter?->id); ?>">
                <i class="ri-add-line"></i> Add Sub-category
            </button>
        <?php endif; ?>
    </div>

    <div class="alert alert-light border small">
        A sub-category always belongs to one category, and the product form offers only the chosen category's
        sub-categories. A sub-category can only be deleted once no product uses it, and it can't move to another category
        while products use it. Categories themselves are managed in the <a href="<?php echo e(route('admin.categories.index')); ?>">Categories</a> tab.
        Built-in sub-categories (such as the BTS members) are looked up by name by the shop pages: you can edit them, but you'll be warned.
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Sub-category</th>
                        <th>Products</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $storefront = $sub->isStorefrontLinked(); ?>
                        <tr>
                            <td data-label="Category"><?php echo e($sub->category->name); ?></td>
                            <td data-label="Sub-category" class="fw-semibold"><?php echo e($sub->name); ?></td>
                            <td data-label="Products"><?php echo e($sub->products_count); ?></td>
                            <td class="text-end text-nowrap stack-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-edit-sub-category
                                        data-id="<?php echo e($sub->id); ?>" data-name="<?php echo e($sub->name); ?>"
                                        data-category-id="<?php echo e($sub->category_id); ?>" data-products="<?php echo e($sub->products_count); ?>"
                                        data-storefront="<?php echo e($storefront ? 1 : 0); ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                
                                <form action="<?php echo e(route('admin.sub-categories.destroy', $sub)); ?>" method="POST" class="d-inline"
                                      <?php if($sub->products_count === 0): ?>
                                          onsubmit="return confirmAction(this, <?php echo e(json_encode(
                                              "Delete the sub-category \"{$sub->name}\"?"
                                              . ($storefront ? ' The shop pages look this sub-category up by name.' : '')
                                          )); ?>);"
                                      <?php endif; ?>>
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">
                            <?php if($categories->isEmpty()): ?>
                                Add a <a href="<?php echo e(route('admin.categories.index')); ?>">category</a> first, then its sub-categories.
                            <?php elseif($filter): ?>
                                <?php echo e($filter->name); ?> has no sub-categories yet. Products can still be filed straight under it.
                            <?php else: ?>
                                No sub-categories yet.
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="modal fade" id="subCategoryModal" tabindex="-1" aria-labelledby="subCategoryModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="subCategoryForm" method="POST" action="<?php echo e(route('admin.sub-categories.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="subCategoryFormMethod" value="POST">
                    <input type="hidden" name="form" value="sub-category">
                    <input type="hidden" name="editing_id" id="subCategoryEditingId" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="subCategoryModalTitle">Add Sub-category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if(old('form') === 'sub-category' && $errors->any()): ?>
                            <div class="alert alert-danger small"><?php echo e($errors->first()); ?></div>
                        <?php endif; ?>
                        <div class="alert alert-warning small d-none" id="subCategoryStorefrontNote">
                            The shop pages look this sub-category up by name. Renaming it can break the filter or page that uses it until you rename it back.
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="subCategoryCategory">Category</label>
                            <select name="category_id" id="subCategoryCategory" class="form-select" required>
                                <option value="">Select category</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <input type="hidden" name="category_id" id="subCategoryCategoryFixed" disabled>
                            <div class="form-text d-none" id="subCategoryCategoryNote">It can't move to another category while products use it.</div>
                        </div>
                        <div>
                            <label class="form-label" for="subCategoryName">Name</label>
                            <input type="text" name="name" id="subCategoryName" class="form-control" maxlength="255" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
const subCategoryStoreUrl = <?php echo json_encode(route('admin.sub-categories.store'), 15, 512) ?>;
const subCategoryModalEl  = document.getElementById('subCategoryModal');
const categorySelect      = document.getElementById('subCategoryCategory');
const categoryFixed       = document.getElementById('subCategoryCategoryFixed');

/* Fill and show the dialog: empty to add, or a saved sub-category to edit; `products` > 0 pins it to its category. */
function openSubCategoryModal({ id = '', name = '', categoryId = '', products = 0, storefront = false } = {}) {
    const pinned = Boolean(id) && Number(products) > 0;

    document.getElementById('subCategoryModalTitle').textContent = id ? 'Edit Sub-category' : 'Add Sub-category';
    document.getElementById('subCategoryFormMethod').value = id ? 'PUT' : 'POST';
    document.getElementById('subCategoryEditingId').value  = id;
    document.getElementById('subCategoryForm').action      = id ? subCategoryStoreUrl + '/' + id : subCategoryStoreUrl;
    document.getElementById('subCategoryName').value       = name;
    document.getElementById('subCategoryStorefrontNote').classList.toggle('d-none', !storefront);

    categorySelect.value    = categoryId;
    categorySelect.disabled = pinned;
    categoryFixed.value     = categoryId;
    categoryFixed.disabled  = !pinned;
    document.getElementById('subCategoryCategoryNote').classList.toggle('d-none', !pinned);

    bootstrap.Modal.getOrCreateInstance(subCategoryModalEl).show();
}

document.addEventListener('click', (event) => {
    const add  = event.target.closest('[data-add-sub-category]');
    const edit = event.target.closest('[data-edit-sub-category]');

    if (add)  openSubCategoryModal({ categoryId: add.dataset.categoryId });
    if (edit) openSubCategoryModal({
        id: edit.dataset.id, name: edit.dataset.name, categoryId: edit.dataset.categoryId,
        products: edit.dataset.products, storefront: edit.dataset.storefront === '1',
    });
});

/* The dialog focuses its name field once open */
subCategoryModalEl.addEventListener('shown.bs.modal', () => document.getElementById('subCategoryName').focus());

<?php if(old('form') === 'sub-category' && $errors->any()): ?>
    // Save was rejected: reopen the dialog as the admin left it
    // (empty fields come back as null, hence the ?? '')
    document.addEventListener('DOMContentLoaded', () => openSubCategoryModal({
        id: <?php echo json_encode(old('editing_id') ?? '', 15, 512) ?>, name: <?php echo json_encode(old('name') ?? '', 15, 512) ?>, categoryId: <?php echo json_encode(old('category_id') ?? '', 15, 512) ?>,
    }));
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/sub-categories/index.blade.php ENDPATH**/ ?>