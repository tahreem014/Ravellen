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
                        <th>Discount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $thumb = $product->images->firstWhere('image', '!=', null);
                            $prices = $product->variants->map(fn ($variant) => $variant->effectivePrice());
                            $discount = $product->discountLabel();
                            $discountPercent = $product->variants->map(fn ($variant) => $variant->discountPercent())->filter()->max();
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
                                    <span data-row-price>PKR <?php echo e(number_format($prices->min())); ?><?php if($prices->min() != $prices->max()): ?> &ndash; <?php echo e(number_format($prices->max())); ?><?php endif; ?></span>
                                    <?php if($product->usesPackPricing()): ?><div class="text-muted small">Mystery Balls &middot; <?php echo e($product->pricing_mode); ?> pricing</div><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <span data-row-discount>
                                    <?php if($discount): ?>
                                        <span class="badge rounded-pill me-1" style="background:#e11d48;"><?php echo e($discount); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small me-1">None</span>
                                    <?php endif; ?>
                                </span>
                                <?php if($product->variants->isNotEmpty()): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#discountModal"
                                            data-action="<?php echo e(route('admin.products.sale', $product)); ?>"
                                            data-name="<?php echo e($product->name); ?>"
                                            data-percent="<?php echo e($discountPercent); ?>"
                                            data-on-sale="<?php echo e($discount ? 1 : 0); ?>">
                                        <i class="ri-price-tag-3-line"></i> <?php echo e($discount ? 'Change' : 'Set discount'); ?>

                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-sm btn-outline-secondary"><i class="ri-edit-line"></i></a>
                                <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Delete this product and all its variants/images?');">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No products yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($products->links()); ?></div>

    
    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="discountForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="discountModalTitle">Set discount</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-semibold mb-3" id="discountProductName"></p>
                        <label class="form-label" for="discountPercent">Discount</label>
                        <div class="input-group" style="max-width:220px;">
                            <input type="number" name="percent" id="discountPercent" min="0.01" max="99.99" step="0.01" class="form-control" placeholder="e.g. 15" required>
                            <span class="input-group-text">% off</span>
                        </div>
                        <div class="form-text">
                            Applies to every variant / pack of this product. Customers see the sale price, the old price crossed out,
                            and a "-15%" style badge on the product image.
                        </div>
                        <div class="text-danger small mt-2" id="discountError" hidden></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="endDiscountForm" class="btn btn-outline-danger me-auto" id="endDiscountButton">End discount</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin-primary">Apply discount</button>
                    </div>
                </form>
                <form method="POST" id="endDiscountForm"><?php echo csrf_field(); ?></form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    const discountModal = document.getElementById('discountModal');
    const discountError = document.getElementById('discountError');
    let discountButton = null;   // the row button that opened the dialog

    discountModal.addEventListener('show.bs.modal', (event) => {
        discountButton = event.relatedTarget;
        document.getElementById('discountForm').action    = discountButton.dataset.action;
        document.getElementById('endDiscountForm').action = discountButton.dataset.action;
        document.getElementById('discountProductName').textContent = discountButton.dataset.name;
        document.getElementById('discountPercent').value  = discountButton.dataset.percent || '';
        document.getElementById('endDiscountButton').hidden = discountButton.dataset.onSale !== '1';
        discountError.hidden = true;
    });

    discountModal.addEventListener('shown.bs.modal', () => document.getElementById('discountPercent').focus());

    // Save in the background and update the row in place — the page never changes.
    async function saveDiscount(event) {
        event.preventDefault();
        const form = event.target;
        const buttons = discountModal.querySelectorAll('button[type=submit]');
        buttons.forEach(b => b.disabled = true);
        discountError.hidden = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (res.status === 419 || res.status === 401) {
                showToast('Your session expired. Reloading the page so you can try again.', 'error');
                setTimeout(() => location.reload(), 1500);
                return;
            }
            const data = await res.json();
            if (!res.ok) {
                discountError.textContent = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save the discount.');
                discountError.hidden = false;
                return;
            }

            const row = discountButton.closest('tr');
            row.querySelector('[data-row-discount]').innerHTML = data.discount
                ? `<span class="badge rounded-pill me-1" style="background:#e11d48;"></span>`
                : '<span class="text-muted small me-1">None</span>';
            if (data.discount) row.querySelector('[data-row-discount] .badge').textContent = data.discount;
            const price = row.querySelector('[data-row-price]');
            if (price) {
                const fmt = (n) => Math.round(n).toLocaleString();
                price.textContent = 'PKR ' + fmt(data.price_min) + (data.price_min !== data.price_max ? ' – ' + fmt(data.price_max) : '');
            }
            discountButton.dataset.percent = data.percent ?? '';
            discountButton.dataset.onSale  = data.discount ? '1' : '0';
            discountButton.innerHTML = `<i class="ri-price-tag-3-line"></i> ${data.discount ? 'Change' : 'Set discount'}`;

            bootstrap.Modal.getInstance(discountModal).hide();
            showToast(data.message, 'success');
        } catch (e) {
            discountError.textContent = 'Could not reach the server. Please try again.';
            discountError.hidden = false;
        } finally {
            buttons.forEach(b => b.disabled = false);
        }
    }

    document.getElementById('discountForm').addEventListener('submit', saveDiscount);
    document.getElementById('endDiscountForm').addEventListener('submit', saveDiscount);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/index.blade.php ENDPATH**/ ?>