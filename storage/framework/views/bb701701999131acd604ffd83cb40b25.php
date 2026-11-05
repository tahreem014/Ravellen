
<?php
    $isPackProduct = $product?->usesPackPricing() ?? false;
    $tableVariants = $product
        ? ($isPackProduct ? $product->variants->sortBy(fn ($variant) => $variant->packSize() ?? PHP_INT_MAX) : $product->variants)
        : collect();
    $markedForRemoval = array_map('intval', old('remove_variants', []));
    $newRows = array_values(old('new_variants', [[]]));   // always at least one blank row to type into
    $statusLabel = fn (string $status) => ucfirst(str_replace('_', ' ', $status));
    $invalid = fn (string $key) => $errors->has($key) ? 'is-invalid' : '';
?>

<div class="admin-card card mb-4" id="variantsCard">
    <div class="card-body">
        <h5 class="mb-3"><?php echo e($isPackProduct ? 'Packs' : 'Variants'); ?></h5>

        <?php if($isPackProduct): ?>
            <p class="small text-muted mb-2">Pack prices come from the Pricing settings above. Stock and availability can still be set per pack.</p>
        <?php endif; ?>

        <?php if($tableVariants->isNotEmpty()): ?>
            <div class="table-responsive mb-3">
                <table class="table table-stack align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price (PKR)</th>
                            <th>Sale Price</th>
                            <?php if (! ($isPackProduct)): ?>
                                <th>Min</th>
                                <th>Max</th>
                            <?php endif; ?>
                            <th>Stock</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tableVariants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $id = $variant->id;
                                $value = fn (string $field) => old("variants.$id.$field", $variant->{$field});
                                $removed = in_array($id, $markedForRemoval, true);
                            ?>
                            <tr data-variant-row class="<?php echo e($removed ? 'opacity-50' : ''); ?>">
                                <?php if($isPackProduct): ?>
                                    <td class="fw-semibold stack-title"><?php echo e($variant->variant_name); ?></td>
                                    <td data-label="Price (PKR)"><?php echo e(number_format($variant->price)); ?></td>
                                    <td data-label="Sale Price">
                                        <?php if($variant->hasDiscount()): ?>
                                            <span class="text-danger fw-semibold"><?php echo e(number_format($variant->sale_price)); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td data-label="Name"><input type="text" name="variants[<?php echo e($id); ?>][variant_name]" value="<?php echo e($value('variant_name')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.variant_name")); ?>" required></td>
                                    <td data-label="Price (PKR)"><input type="number" step="0.01" name="variants[<?php echo e($id); ?>][price]" value="<?php echo e($value('price')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.price")); ?>" style="width:110px;" required></td>
                                    <td data-label="Sale Price"><input type="number" step="0.01" name="variants[<?php echo e($id); ?>][sale_price]" value="<?php echo e($value('sale_price')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.sale_price")); ?>" style="width:110px;" placeholder="No sale"></td>
                                    <td data-label="Min"><input type="number" name="variants[<?php echo e($id); ?>][min_qty]" value="<?php echo e($value('min_qty')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.min_qty")); ?>" style="width:70px;" required></td>
                                    <td data-label="Max"><input type="number" name="variants[<?php echo e($id); ?>][max_qty]" value="<?php echo e($value('max_qty')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.max_qty")); ?>" style="width:70px;" required></td>
                                <?php endif; ?>
                                <td data-label="Stock"><input type="number" name="variants[<?php echo e($id); ?>][stock]" value="<?php echo e($value('stock')); ?>" class="form-control form-control-sm <?php echo e($invalid("variants.$id.stock")); ?>" style="width:80px;" required></td>
                                <td data-label="Status">
                                    <select name="variants[<?php echo e($id); ?>][status]" class="form-select form-select-sm <?php echo e($invalid("variants.$id.status")); ?>">
                                        <?php $__currentLoopData = \App\Models\ProductVariant::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status); ?>" <?php if($value('status') === $status): echo 'selected'; endif; ?>><?php echo e($statusLabel($status)); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td class="text-end text-nowrap stack-actions">
                                    <?php if (! ($isPackProduct)): ?>
                                        <input type="hidden" name="remove_variants[]" value="<?php echo e($id); ?>" <?php if(! $removed): echo 'disabled'; endif; ?>>
                                        <button type="button" class="btn btn-sm <?php echo e($removed ? 'btn-danger' : 'btn-outline-danger'); ?>" data-toggle-variant-removal
                                                title="<?php echo e($removed ? 'Keep this variant' : 'Remove this variant'); ?>"><i class="<?php echo e($removed ? 'ri-arrow-go-back-line' : 'ri-delete-bin-line'); ?>"></i></button>
                                        <span class="small text-danger ms-1" data-removal-note <?php if(! $removed): ?> hidden <?php endif; ?>>Removed when you save</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($product): ?>
            <p class="small text-muted mb-3">No variants yet — add one below.</p>
        <?php endif; ?>

        <?php if (! ($isPackProduct)): ?>
            
            <div data-standard-only>
                <div class="fw-semibold small mb-2"><?php echo e($product ? 'Add variants' : 'Variants'); ?> <span class="text-muted fw-normal">— rows left blank are ignored</span></div>
                <div class="table-responsive">
                    <table class="table table-stack table-sm align-middle mb-2">
                        <thead>
                            <tr><th>Name</th><th>Price (PKR)</th><th>Sale price <span class="text-muted fw-normal">(optional)</span></th><th>Min</th><th>Max</th><th>Stock</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody id="newVariantRows">
                            <?php $__currentLoopData = $newRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $n = "new_variants.$i"; ?>
                                <tr>
                                    <td data-label="Name"><input type="text" name="new_variants[<?php echo e($i); ?>][variant_name]" value="<?php echo e($row['variant_name'] ?? ''); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.variant_name")); ?>"></td>
                                    <td data-label="Price (PKR)"><input type="number" step="0.01" name="new_variants[<?php echo e($i); ?>][price]" value="<?php echo e($row['price'] ?? ''); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.price")); ?>" style="width:110px;"></td>
                                    <td data-label="Sale price"><input type="number" step="0.01" name="new_variants[<?php echo e($i); ?>][sale_price]" value="<?php echo e($row['sale_price'] ?? ''); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.sale_price")); ?>" style="width:110px;" placeholder="No sale"></td>
                                    <td data-label="Min"><input type="number" name="new_variants[<?php echo e($i); ?>][min_qty]" value="<?php echo e($row['min_qty'] ?? 1); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.min_qty")); ?>" style="width:70px;"></td>
                                    <td data-label="Max"><input type="number" name="new_variants[<?php echo e($i); ?>][max_qty]" value="<?php echo e($row['max_qty'] ?? 1); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.max_qty")); ?>" style="width:70px;"></td>
                                    <td data-label="Stock"><input type="number" name="new_variants[<?php echo e($i); ?>][stock]" value="<?php echo e($row['stock'] ?? 0); ?>" class="form-control form-control-sm <?php echo e($invalid("$n.stock")); ?>" style="width:80px;"></td>
                                    <td data-label="Status">
                                        <select name="new_variants[<?php echo e($i); ?>][status]" class="form-select form-select-sm <?php echo e($invalid("$n.status")); ?>">
                                            <?php $__currentLoopData = \App\Models\ProductVariant::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($status); ?>" <?php if(($row['status'] ?? 'active') === $status): echo 'selected'; endif; ?>><?php echo e($statusLabel($status)); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td class="text-end stack-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-remove-new-variant title="Remove this row"><i class="ri-close-line"></i></button></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="addVariantRow"><i class="ri-add-line"></i> Add another variant</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! ($isPackProduct)): ?>
<template id="newVariantTemplate">
    <tr>
        <td data-label="Name"><input type="text" name="new_variants[__INDEX__][variant_name]" class="form-control form-control-sm"></td>
        <td data-label="Price (PKR)"><input type="number" step="0.01" name="new_variants[__INDEX__][price]" class="form-control form-control-sm" style="width:110px;"></td>
        <td data-label="Sale price"><input type="number" step="0.01" name="new_variants[__INDEX__][sale_price]" class="form-control form-control-sm" style="width:110px;" placeholder="No sale"></td>
        <td data-label="Min"><input type="number" name="new_variants[__INDEX__][min_qty]" value="1" class="form-control form-control-sm" style="width:70px;"></td>
        <td data-label="Max"><input type="number" name="new_variants[__INDEX__][max_qty]" value="1" class="form-control form-control-sm" style="width:70px;"></td>
        <td data-label="Stock"><input type="number" name="new_variants[__INDEX__][stock]" value="0" class="form-control form-control-sm" style="width:80px;"></td>
        <td data-label="Status">
            <select name="new_variants[__INDEX__][status]" class="form-select form-select-sm">
                <?php $__currentLoopData = \App\Models\ProductVariant::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>"><?php echo e($statusLabel($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </td>
        <td class="text-end stack-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-remove-new-variant title="Remove this row"><i class="ri-close-line"></i></button></td>
    </tr>
</template>
<?php endif; ?>

<script>
(function () {
    const card = document.getElementById('variantsCard');
    let nextIndex = 1000;   // keeps new row names unique next to the server's 0, 1, 2 …

    card.addEventListener('click', (e) => {
        // an existing variant: mark / unmark it for removal (nothing is deleted until the form is saved)
        const toggle = e.target.closest('[data-toggle-variant-removal]');
        if (toggle) {
            const row = toggle.closest('[data-variant-row]');
            const marker = row.querySelector('input[name="remove_variants[]"]');
            marker.disabled = !marker.disabled;
            const removed = !marker.disabled;
            row.classList.toggle('opacity-50', removed);
            row.querySelector('[data-removal-note]').hidden = !removed;
            toggle.classList.toggle('btn-danger', removed);
            toggle.classList.toggle('btn-outline-danger', !removed);
            toggle.title = removed ? 'Keep this variant' : 'Remove this variant';
            toggle.querySelector('i').className = removed ? 'ri-arrow-go-back-line' : 'ri-delete-bin-line';
        }

        // rows to add
        const template = document.getElementById('newVariantTemplate');
        const rows = document.getElementById('newVariantRows');
        if (e.target.closest('#addVariantRow')) {
            rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        }
        const removeNew = e.target.closest('[data-remove-new-variant]');
        if (removeNew) removeNew.closest('tr').remove();
    });

    // Variants only apply to standard pricing: Mystery Balls pricing builds the packs itself
    function showForMode(mode) {
        card.querySelectorAll('[data-standard-only]').forEach(part => {
            const standard = mode === 'standard';
            part.hidden = !standard;
            part.querySelectorAll('input, select, button').forEach(el => el.disabled = !standard);
        });
    }

    document.addEventListener('pricing:mode', (e) => showForMode(e.detail.mode));
    showForMode(document.querySelector('input[name="pricing_mode"]:checked')?.value || 'standard');
})();
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/partials/variant-fields.blade.php ENDPATH**/ ?>