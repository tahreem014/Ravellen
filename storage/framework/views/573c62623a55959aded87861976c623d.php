<?php $__env->startSection('title', 'Edit Product'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="<?php echo e(route('admin.products.index')); ?>" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-admin-primary btn-sm"><i class="ri-add-line"></i> Add Another Product</a>
    </div>

    <div class="admin-card card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Product Details</h5>
            <form action="<?php echo e(route('admin.products.update', $product)); ?>" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $product->description)); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if($product->category_id == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sub-category (optional)</label>
                        <select name="sub_category_id" id="subCategorySelect" class="form-select"></select>
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="admin-card card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Variants</h5>
            <div class="table-responsive mb-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price (PKR)</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $formId = 'variant-form-' . $variant->id; ?>
                            <tr>
                                <td><input form="<?php echo e($formId); ?>" type="text" name="variant_name" value="<?php echo e($variant->variant_name); ?>" class="form-control form-control-sm" required></td>
                                <td><input form="<?php echo e($formId); ?>" type="number" step="0.01" name="price" value="<?php echo e($variant->price); ?>" class="form-control form-control-sm" style="width:110px;" required></td>
                                <td><input form="<?php echo e($formId); ?>" type="number" name="min_qty" value="<?php echo e($variant->min_qty); ?>" class="form-control form-control-sm" style="width:70px;" required></td>
                                <td><input form="<?php echo e($formId); ?>" type="number" name="max_qty" value="<?php echo e($variant->max_qty); ?>" class="form-control form-control-sm" style="width:70px;" required></td>
                                <td><input form="<?php echo e($formId); ?>" type="number" name="stock" value="<?php echo e($variant->stock); ?>" class="form-control form-control-sm" style="width:80px;" required></td>
                                <td>
                                    <select form="<?php echo e($formId); ?>" name="status" class="form-select form-select-sm">
                                        <?php $__currentLoopData = ['active','out_of_stock','pre_order','restocking','discontinued']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php if($variant->status === $status): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_',' ', $status))); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form id="<?php echo e($formId); ?>" action="<?php echo e(route('admin.products.variants.update', [$product, $variant])); ?>" method="POST" class="d-none">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                    </form>
                                    <button form="<?php echo e($formId); ?>" class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-save-line"></i></button>
                                    <form action="<?php echo e(route('admin.products.variants.destroy', [$product, $variant])); ?>" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Remove this variant?');">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No variants yet — add one below.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <form action="<?php echo e(route('admin.products.variants.store', $product)); ?>" method="POST" class="row g-2 align-items-end">
                <?php echo csrf_field(); ?>
                <div class="col-md-3"><label class="form-label small">Name</label><input type="text" name="variant_name" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Price</label><input type="number" step="0.01" name="price" class="form-control form-control-sm" required></div>
                <div class="col-md-1"><label class="form-label small">Min</label><input type="number" name="min_qty" value="1" class="form-control form-control-sm" required></div>
                <div class="col-md-1"><label class="form-label small">Max</label><input type="number" name="max_qty" value="1" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Stock</label><input type="number" name="stock" value="0" class="form-control form-control-sm" required></div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <?php $__currentLoopData = ['active','out_of_stock','pre_order','restocking','discontinued']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>"><?php echo e(ucfirst(str_replace('_',' ', $status))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-1"><button class="btn btn-admin-primary btn-sm w-100" type="submit"><i class="ri-add-line"></i></button></div>
            </form>
        </div>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <h5 class="mb-3">Media</h5>
            <div class="row g-3 mb-3">
                <?php $__empty_1 = true; $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="position-relative">
                            <?php if($image->image): ?>
                                <img src="<?php echo e($image->image_url); ?>" class="rounded w-100" style="height:100px;object-fit:cover;">
                            <?php elseif($image->video): ?>
                                <video src="<?php echo e($image->video_url); ?>" class="rounded w-100" style="height:100px;object-fit:cover;" muted></video>
                            <?php endif; ?>
                            <form action="<?php echo e(route('admin.products.images.destroy', [$product, $image])); ?>" method="POST" onsubmit="return confirmAction(this, 'Remove this media?');" class="position-absolute top-0 end-0 m-1">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-danger" style="padding:.1rem .4rem;"><i class="ri-close-line"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-muted">No media uploaded yet.</div>
                <?php endif; ?>
            </div>

            <form action="<?php echo e(route('admin.products.images.store', $product)); ?>" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                <?php echo csrf_field(); ?>
                <div class="col-md-4">
                    <label class="form-label small">Image</label>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">or Video</label>
                    <input type="file" name="video" class="form-control form-control-sm" accept="video/*">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-admin-primary btn-sm" type="submit"><i class="ri-upload-line"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    const categories = <?php echo json_encode($categories->keyBy('id'), 15, 512) ?>;
    const currentSubCategory = <?php echo json_encode($product->sub_category_id, 15, 512) ?>;
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
                if (String(sub.id) === String(currentSubCategory)) opt.selected = true;
                subCategorySelect.appendChild(opt);
            });
        }
    }

    categorySelect.addEventListener('change', populateSubCategories);
    populateSubCategories();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>