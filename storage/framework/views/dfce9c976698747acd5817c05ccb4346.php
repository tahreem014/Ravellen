<?php $__env->startSection('title', 'Add Product'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-3">
        <a href="<?php echo e(route('admin.products.index')); ?>" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <form action="<?php echo e(route('admin.products.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo e(old('description')); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select category</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if(old('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sub-category (optional)</label>
                        <select name="sub_category_id" id="subCategorySelect" class="form-select">
                            <option value="">None</option>
                        </select>
                    </div>
                </div>

                <?php echo $__env->make('admin.products.partials.pricing-fields', ['currentMode' => null, 'hasVariants' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-semibold mb-2">Photos &amp; videos <span class="text-muted fw-normal small">(optional)</span></div>
                    <?php echo $__env->make('admin.products.partials.media-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <button type="submit" class="btn btn-admin-primary" data-media-submit>Create Product</button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    const categories = <?php echo json_encode($categories->keyBy('id'), 15, 512) ?>;
    const oldSubCategory = <?php echo json_encode(old('sub_category_id'), 15, 512) ?>;
    const categorySelect = document.getElementById('categorySelect');
    const subCategorySelect = document.getElementById('subCategorySelect');

    function populateSubCategories() {
        const category = categories[categorySelect.value];
        subCategorySelect.innerHTML = '<option value="">None</option>';
        if (category && category.subCategories) {
            category.subCategories.forEach((sub) => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (String(sub.id) === String(oldSubCategory)) opt.selected = true;
                subCategorySelect.appendChild(opt);
            });
        }
    }

    categorySelect.addEventListener('change', populateSubCategories);
    populateSubCategories();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/create.blade.php ENDPATH**/ ?>