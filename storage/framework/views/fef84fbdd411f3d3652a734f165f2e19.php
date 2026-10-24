
<?php
    $mode  = old('pricing_mode', $currentMode) ?: 'standard';
    $packs = array_values(old('packs', $pricing['packs']));
    $pricingErrors = collect($errors->getMessages())
        ->filter(fn ($messages, $key) => \Illuminate\Support\Str::startsWith($key, ['base_price', 'base_sale_price', 'max_qty', 'packs']))
        ->flatten()->unique();
?>

<div class="border rounded-3 p-3 mb-3" id="pricingSection" data-admin-region="pricing">
    <div class="fw-semibold mb-2">Pricing</div>

    <?php if($pricingErrors->isNotEmpty()): ?>
        <div class="alert alert-danger small py-2">
            <?php $__currentLoopData = $pricingErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($message); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column gap-1 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="pricing_mode" id="pricingStandard" value="" <?php if($mode === 'standard'): echo 'checked'; endif; ?>>
            <label class="form-check-label" for="pricingStandard">
                Standard <span class="text-muted small">— set a price for each variant (sizes, colours …)</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="pricing_mode" id="pricingAutomatic" value="automatic" <?php if($mode === 'automatic'): echo 'checked'; endif; ?>>
            <label class="form-check-label" for="pricingAutomatic">
                Mystery Balls · Automatic <span class="text-muted small">— one price for 5 balls; bigger packs are calculated for you</span>
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="pricing_mode" id="pricingCustom" value="custom" <?php if($mode === 'custom'): echo 'checked'; endif; ?>>
            <label class="form-check-label" for="pricingCustom">
                Mystery Balls · Custom <span class="text-muted small">— set your own price for each quantity (5, 10, 15 …)</span>
            </label>
        </div>
    </div>

    <div data-pricing-panel="standard" class="small text-muted">
        Prices are set per variant in the <strong>Variants</strong> section<?php echo e(isset($product) ? ' below' : ' after you create the product'); ?>.
    </div>

    <div data-pricing-panel="automatic">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small mb-1">Price for 5 balls (PKR)</label>
                <input type="number" step="0.01" min="0" name="base_price" value="<?php echo e(old('base_price', $pricing['base_price'])); ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Sale price for 5 balls <span class="text-muted">(optional)</span></label>
                <input type="number" step="0.01" min="0" name="base_sale_price" value="<?php echo e(old('base_sale_price', $pricing['base_sale_price'])); ?>" class="form-control" placeholder="No sale">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Sell up to</label>
                <select name="max_qty" class="form-select">
                    <?php for($qty = 5; $qty <= 100; $qty += 5): ?>
                        <option value="<?php echo e($qty); ?>" <?php if((int) old('max_qty', $pricing['max_qty']) === $qty): echo 'selected'; endif; ?>><?php echo e($qty); ?> balls</option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="small text-muted mt-2 mb-1">Customers will see:</div>
        <div class="d-flex flex-wrap gap-2" id="autoPricePreview"></div>
    </div>

    <div data-pricing-panel="custom">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-2">
                <thead>
                    <tr>
                        <th>Balls</th>
                        <th>Price (PKR)</th>
                        <th>Sale price <span class="text-muted fw-normal">(optional)</span></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="customPackRows">
                    <?php $__currentLoopData = $packs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $pack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><input type="number" name="packs[<?php echo e($i); ?>][qty]" value="<?php echo e($pack['qty'] ?? ''); ?>" step="5" min="5" max="100" class="form-control form-control-sm" style="width:90px;" required></td>
                            <td><input type="number" name="packs[<?php echo e($i); ?>][price]" value="<?php echo e($pack['price'] ?? ''); ?>" step="0.01" min="0" class="form-control form-control-sm" style="width:130px;" required></td>
                            <td><input type="number" name="packs[<?php echo e($i); ?>][sale_price]" value="<?php echo e($pack['sale_price'] ?? ''); ?>" step="0.01" min="0" class="form-control form-control-sm" style="width:130px;" placeholder="No sale"></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-pack title="Remove this quantity"><i class="ri-close-line"></i></button></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="addPackRow"><i class="ri-add-line"></i> Add quantity</button>
    </div>

    <?php if($hasVariants): ?>
        <div class="small text-warning-emphasis mt-2" data-pricing-switch-note>
            <i class="ri-information-line"></i> Saving with Mystery Balls pricing replaces this product's current variants with packs of balls.
        </div>
    <?php endif; ?>
</div>

<template id="packRowTemplate">
    <tr>
        <td><input type="number" name="packs[__INDEX__][qty]" value="__QTY__" step="5" min="5" max="100" class="form-control form-control-sm" style="width:90px;" required></td>
        <td><input type="number" name="packs[__INDEX__][price]" step="0.01" min="0" class="form-control form-control-sm" style="width:130px;" required></td>
        <td><input type="number" name="packs[__INDEX__][sale_price]" step="0.01" min="0" class="form-control form-control-sm" style="width:130px;" placeholder="No sale"></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-pack title="Remove this quantity"><i class="ri-close-line"></i></button></td>
    </tr>
</template>

<script>
(function () {
    // Listeners sit on the section itself (not its contents): on the edit page the
    // contents are re-rendered after an in-place save elsewhere (data-admin-region).
    const section  = document.getElementById('pricingSection');
    const template = document.getElementById('packRowTemplate');
    let nextIndex  = 1000;   // keeps new row names unique next to the server's 0, 1, 2 …

    const rows    = () => document.getElementById('customPackRows');
    const preview = () => document.getElementById('autoPricePreview');
    const field   = (name) => section.querySelector(`[name="${name}"]`);
    const fmt   = (n) => 'PKR ' + Math.round(n).toLocaleString();

    function currentMode() {
        return section.querySelector('input[name="pricing_mode"]:checked')?.value || 'standard';
    }

    // Only the chosen mode's fields are submitted (and validated by the browser).
    function showPanel() {
        const mode = currentMode();
        section.querySelectorAll('[data-pricing-panel]').forEach(panel => {
            const active = panel.dataset.pricingPanel === mode;
            panel.hidden = !active;
            panel.querySelectorAll('input, select, button').forEach(el => el.disabled = !active);
        });
        const note = section.querySelector('[data-pricing-switch-note]');
        if (note) note.hidden = mode === 'standard';
        renderPreview();
    }

    function renderPreview() {
        const base = parseFloat(field('base_price').value);
        const sale = parseFloat(field('base_sale_price').value);
        const max  = parseInt(field('max_qty').value, 10);

        if (!(base > 0)) {
            preview().innerHTML = '<span class="small text-muted">Enter the price for 5 balls to see every pack price.</span>';
            return;
        }

        const onSale = sale > 0 && sale < base;
        let html = '';
        for (let qty = 5; qty <= max; qty += 5) {
            const steps = qty / 5;
            html += `<span class="badge text-bg-light border fw-normal">${qty} balls: <strong>${fmt((onSale ? sale : base) * steps)}</strong>`
                  + (onSale ? ` <s class="text-muted">${fmt(base * steps)}</s>` : '') + '</span>';
        }
        preview().innerHTML = html;
    }

    section.addEventListener('change', (e) => {
        if (e.target.name === 'pricing_mode') showPanel();
    });
    section.addEventListener('input', (e) => {
        if (['base_price', 'base_sale_price', 'max_qty'].includes(e.target.name)) renderPreview();
    });

    section.addEventListener('click', (e) => {
        if (e.target.closest('#addPackRow')) {
            const quantities = [...rows().querySelectorAll('input[name$="[qty]"]')].map(input => parseInt(input.value, 10) || 0);
            const nextQty = Math.min(100, Math.max(0, ...quantities) + 5);
            rows().insertAdjacentHTML('beforeend', template.innerHTML
                .replaceAll('__INDEX__', nextIndex++)
                .replaceAll('__QTY__', nextQty));
        }

        const remove = e.target.closest('[data-remove-pack]');
        if (remove && rows().children.length > 1) remove.closest('tr').remove();
    });

    document.addEventListener('admin:regions-updated', showPanel);
    showPanel();
})();
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/partials/pricing-fields.blade.php ENDPATH**/ ?>