<?php $__env->startSection('title', 'Products'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control form-control-sm" placeholder="Search products..." style="min-width:220px;">
            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-search-line"></i></button>
        </form>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-admin-primary btn-sm">
            <i class="ri-add-line"></i> Add Product
        </a>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Variants</th>
                        <th>Price Range</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $thumb = $product->images->firstWhere('image', '!=', null);
                            $prices = $product->variants->pluck('price');
                        ?>
                        <tr>
                            <td style="width:56px;">
                                <?php if($thumb): ?>
                                    <img src="<?php echo e($thumb->image_url); ?>" alt="" class="rounded" style="width:44px;height:44px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                        <i class="ri-image-line text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?php echo e($product->name); ?></div>
                            </td>
                            <td>
                                <?php echo e($product->category?->name); ?>

                                <?php if($product->subCategory): ?>
                                    <span class="text-muted small">/ <?php echo e($product->subCategory->name); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($product->variants->count()); ?></td>
                            <td>
                                <?php if($prices->isNotEmpty()): ?>
                                    PKR <?php echo e(number_format($prices->min())); ?><?php if($prices->min() != $prices->max()): ?> &ndash; <?php echo e(number_format($prices->max())); ?><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-sm btn-outline-secondary"><i class="ri-edit-line"></i></a>
                                <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Delete this product and all its variants/images?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No products yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($products->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/index.blade.php ENDPATH**/ ?>