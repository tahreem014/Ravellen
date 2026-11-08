<?php $__env->startSection('title', 'Edit Product'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="<?php echo e(route('admin.products.index')); ?>" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-admin-primary btn-sm"><i class="ri-add-line"></i> Add Another Product</a>
    </div>

    
    <form action="<?php echo e(route('admin.products.update', $product)); ?>" method="POST" enctype="multipart/form-data" id="productForm">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        
        <?php
            $discount = $product->discountLabel();
            $discountPercent = $product->variants->map(fn ($variant) => $variant->discountPercent())->filter()->max();
        ?>
        <div class="admin-card card mb-4" id="discount">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h5 class="mb-0"><i class="ri-price-tag-3-line text-danger"></i> Discount</h5>
                    <?php if($discount): ?>
                        <span class="badge rounded-pill" style="background:#e11d48;"><?php echo e($discount); ?></span>
                        <span class="small text-muted">on sale now</span>
                    <?php else: ?>
                        <span class="small text-muted">No discount running</span>
                    <?php endif; ?>
                </div>

                <?php if($product->variants->isEmpty()): ?>
                    <p class="small text-muted mb-0">Add a variant below first, then you can put this product on sale.</p>
                <?php else: ?>
                    <div class="input-group input-group-sm" style="width:170px;">
                        <input type="number" name="discount_percent" value="<?php echo e(old('discount_percent', $discountPercent)); ?>" min="0.01" max="99.99" step="0.01" class="form-control" placeholder="e.g. 15" aria-label="Discount percentage">
                        <span class="input-group-text">% off</span>
                    </div>
                    <div class="form-text">
                        Takes the percentage off every <?php echo e($product->usesPackPricing() ? 'pack' : 'variant'); ?> of this product when you save; clear the box to end the discount.
                        <?php if($product->pricing_mode === \App\Models\Product::PRICING_AUTOMATIC): ?>
                            Every pack follows the 5-ball sale price.
                        <?php elseif($product->pricing_mode === \App\Models\Product::PRICING_CUSTOM): ?>
                            To discount only some quantities, use the sale price boxes in Pricing below.
                        <?php else: ?>
                            To discount just one variant, use its Sale Price in the Variants section below.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Product Details</h5>

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $product->description)); ?></textarea>
                </div>

                <?php echo $__env->make('admin.products.partials.category-fields', ['categoryId' => $product->category_id, 'subCategoryId' => $product->sub_category_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->make('admin.products.partials.pricing-fields', [
                    'currentMode' => $product->pricing_mode,
                    'hasVariants' => ! $product->usesPackPricing() && $product->variants->isNotEmpty(),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <?php echo $__env->make('admin.products.partials.variant-fields', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="admin-card card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Media</h5>

                <?php $mediaMarkedForRemoval = array_map('intval', old('remove_media', [])); ?>
                <div class="row g-3 mb-3" id="existingMedia">
                    <?php $__empty_1 = true; $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $removed = in_array($image->id, $mediaMarkedForRemoval, true); ?>
                        <div class="col-6 col-md-3 col-lg-2" data-media-tile>
                            <div class="position-relative <?php echo e($removed ? 'opacity-50' : ''); ?>">
                                <?php if($image->image): ?>
                                    <img src="<?php echo e($image->image_url); ?>" class="rounded w-100" style="height:100px;object-fit:cover;">
                                <?php elseif($image->video): ?>
                                    <video src="<?php echo e($image->video_url); ?>" class="rounded w-100" style="height:100px;object-fit:cover;" muted></video>
                                    <span class="badge bg-dark position-absolute bottom-0 start-0 m-1"><i class="ri-video-line"></i> Video</span>
                                <?php endif; ?>
                                
                                <input type="hidden" name="remove_media[]" value="<?php echo e($image->id); ?>" <?php if(! $removed): echo 'disabled'; endif; ?>>
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="padding:.1rem .4rem;" data-toggle-media-removal
                                        title="<?php echo e($removed ? 'Keep this file' : 'Remove this file'); ?>"><i class="<?php echo e($removed ? 'ri-arrow-go-back-line' : 'ri-close-line'); ?>"></i></button>
                            </div>
                            <div class="small text-danger mt-1" data-removal-note <?php if(! $removed): ?> hidden <?php endif; ?>>Removed when you save</div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12 text-muted">No media uploaded yet.</div>
                    <?php endif; ?>
                </div>

                <div class="fw-semibold small mb-2">Add photos &amp; videos</div>
                <?php echo $__env->make('admin.products.partials.media-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <button type="submit" class="btn btn-admin-primary" data-media-submit>Update Product</button>
    </form>

    <script>
        // Existing media: the × button marks a file for removal (or takes the mark off); nothing is deleted until the form is saved
        document.getElementById('existingMedia').addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-toggle-media-removal]');
            if (!toggle) return;

            const tile = toggle.closest('[data-media-tile]');
            const marker = tile.querySelector('input[name="remove_media[]"]');
            marker.disabled = !marker.disabled;
            const removed = !marker.disabled;

            tile.querySelector('.position-relative').classList.toggle('opacity-50', removed);
            tile.querySelector('[data-removal-note]').hidden = !removed;
            toggle.title = removed ? 'Keep this file' : 'Remove this file';
            toggle.querySelector('i').className = removed ? 'ri-arrow-go-back-line' : 'ri-close-line';
        });
    </script>

    <script>
        /**
         * Live stock in the Variants table: orders placed while this page is open lower each variant's
         * stock (and a sold-out one turns Out of stock). Every few seconds the Stock and Status fields are
         * brought up to date — except one the admin has changed: that keeps their edit, with the live
         * figure shown under it, so saving never silently undoes a sale they hadn't seen yet.
         */
        (function () {
            const STOCK_URL = <?php echo json_encode(route('admin.products.stock', ['ids' => [$product->id]], false)) ?>;
            const PRODUCT_ID = <?php echo json_encode((string) $product->id, 15, 512) ?>;
            const POLL_MS = 5000;
            let polling = false;

            function note(field, text) {
                let el = field.parentElement.querySelector('[data-live-hint]');
                if (!el) {
                    if (!text) return;
                    el = document.createElement('div');
                    el.setAttribute('data-live-hint', '');
                    el.className = 'small text-danger mt-1';
                    field.insertAdjacentElement('afterend', el);
                }
                el.textContent = text;
                el.hidden = !text;
            }

            function sync(field, value) {
                value = String(value);
                const label = field.tagName === 'SELECT' ? (field.querySelector(`option[value="${value}"]`)?.textContent || value) : value;

                if (field.value === field.dataset.synced) {   // untouched: follows the database
                    if (field.value !== value) {
                        field.value = value;
                        field.classList.add('border-warning');
                        setTimeout(() => field.classList.remove('border-warning'), 1500);
                    }
                    field.dataset.synced = value;
                    note(field, '');
                } else {                                      // edited by the admin: keep it, show the live figure
                    note(field, field.value === value ? '' : 'Live: ' + label);
                }
            }

            async function pollStock() {
                if (polling || document.visibilityState !== 'visible') return;
                polling = true;

                try {
                    const res = await fetch(STOCK_URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
                    if (!res.ok) return;
                    const variants = (await res.json()).products[PRODUCT_ID]?.variants || {};

                    Object.entries(variants).forEach(([id, fresh]) => {
                        const stock  = document.querySelector(`input[name="variants[${id}][stock]"]`);
                        const status = document.querySelector(`select[name="variants[${id}][status]"]`);
                        if (stock)  sync(stock, fresh.stock);
                        if (status) sync(status, fresh.status);
                    });
                } catch (e) {
                    // offline, signed out or server busy — try again on the next tick
                } finally {
                    polling = false;
                }
            }

            setInterval(pollStock, POLL_MS);
            document.addEventListener('visibilitychange', pollStock);
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>