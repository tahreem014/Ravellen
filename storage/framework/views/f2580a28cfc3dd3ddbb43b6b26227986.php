<?php $__env->startSection('title', 'Shipping Rates'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" action="<?php echo e(route('admin.shipping-rates.index')); ?>" class="d-flex gap-2 flex-grow-1" style="max-width:26rem;">
            <input type="search" name="q" value="<?php echo e($search); ?>" class="form-control form-control-sm" placeholder="Search country or city">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
            <?php if($search !== ''): ?>
                <a href="<?php echo e(route('admin.shipping-rates.index')); ?>" class="btn btn-sm btn-link">Clear</a>
            <?php endif; ?>
        </form>
        <button class="btn btn-admin-primary btn-sm admin-toolbar-btn" onclick="openShippingRateModal(null)">
            <i class="ri-add-line"></i> Add Shipping Rate
        </button>
    </div>

    <div class="alert alert-light border small">
        <strong>How the delivery charge is chosen:</strong> a rate for the customer's city wins; otherwise the country's
        default rate applies; if there is neither, we don't deliver there and checkout is blocked. Add a country's default
        rate once, then add city rates only where a city costs something different.
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Applies to</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $shippingRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="stack-title"><?php echo e($rate->country); ?></td>
                            <td data-label="Applies to">
                                <?php if($rate->isCountryWide()): ?>
                                    <span class="badge bg-primary">Country default</span>
                                    <span class="text-muted small d-block">every city without its own rate</span>
                                <?php else: ?>
                                    <?php echo e($rate->city); ?>

                                <?php endif; ?>
                            </td>
                            <td data-label="Amount">
                                <?php if($rate->isFree()): ?>
                                    <span class="badge bg-success">Free shipping</span>
                                <?php else: ?>
                                    PKR <?php echo e(number_format($rate->amount, 2)); ?>

                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if($rate->is_active): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap stack-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick='openShippingRateModal(<?php echo json_encode($rate, 15, 512) ?>)'>Edit</button>

                                <form action="<?php echo e(route('admin.shipping-rates.toggle-active', $rate)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm btn-outline-warning"><?php echo e($rate->is_active ? 'Deactivate' : 'Activate'); ?></button>
                                </form>

                                <form action="<?php echo e(route('admin.shipping-rates.destroy', $rate)); ?>" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this shipping rate? Past orders keep their recorded shipping fee.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">
                            <?php echo e($search !== '' ? 'No shipping rates match your search.' : 'No shipping rates yet. Add a country\'s default rate to start delivering there.'); ?>

                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($shippingRates->links()); ?></div>

    <div class="modal fade" id="shippingRateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="shippingRateForm" method="POST" action="<?php echo e(route('admin.shipping-rates.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="shippingRateFormMethod" value="POST">
                    <input type="hidden" name="editing_id" id="shippingRateEditingId" value="<?php echo e(old('editing_id')); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shippingRateModalTitle">Add Shipping Rate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger small"><?php echo e($errors->first()); ?></div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label" for="shippingRateCountry">Country</label>
                            <select name="country" id="shippingRateCountry" class="form-select" required>
                                <?php if (isset($component)) { $__componentOriginala5780a67c2c14224fd8654f6923eb903 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala5780a67c2c14224fd8654f6923eb903 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.country-options','data' => ['placeholder' => 'Select a country','selected' => old('country')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('country-options'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['placeholder' => 'Select a country','selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('country'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala5780a67c2c14224fd8654f6923eb903)): ?>
<?php $attributes = $__attributesOriginala5780a67c2c14224fd8654f6923eb903; ?>
<?php unset($__attributesOriginala5780a67c2c14224fd8654f6923eb903); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala5780a67c2c14224fd8654f6923eb903)): ?>
<?php $component = $__componentOriginala5780a67c2c14224fd8654f6923eb903; ?>
<?php unset($__componentOriginala5780a67c2c14224fd8654f6923eb903); ?>
<?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">This rate applies to</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="shippingRateScopeCountry" value="country" checked>
                                <label class="form-check-label" for="shippingRateScopeCountry">The whole country (its default rate)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="shippingRateScopeCity" value="city">
                                <label class="form-check-label" for="shippingRateScopeCity">One city only</label>
                            </div>
                        </div>
                        <div class="mb-3 d-none" id="shippingRateCityWrap">
                            <label class="form-label" for="shippingRateCity">City</label>
                            <select name="city" id="shippingRateCity" class="form-select">
                                <option value="">Select a city</option>
                            </select>
                            <input type="text" id="shippingRateCityOther" class="form-control mt-2" autocomplete="off" placeholder="Type the city's name" style="display:none;">
                            <div class="form-text">Every city of the chosen country is listed. For a place that isn't listed, choose "Other" at the bottom and type its name.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="shippingRateAmount">Delivery charge (PKR)</label>
                            <input type="number" step="0.01" min="0" name="amount" id="shippingRateAmount" class="form-control" value="<?php echo e(old('amount')); ?>" required>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="free_shipping" value="1" class="form-check-input" id="shippingRateFree" <?php echo e(old('free_shipping') ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="shippingRateFree">Free shipping</label>
                            </div>
                            <div class="form-text">Customers see a green "Free shipping" badge instead of an amount.</div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="shippingRateIsActive" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="shippingRateIsActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-admin-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<?php if (isset($component)) { $__componentOriginalc51112e1d1a9b8149b3efe3798a57b42 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc51112e1d1a9b8149b3efe3798a57b42 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.location-picker-js','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('location-picker-js'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc51112e1d1a9b8149b3efe3798a57b42)): ?>
<?php $attributes = $__attributesOriginalc51112e1d1a9b8149b3efe3798a57b42; ?>
<?php unset($__attributesOriginalc51112e1d1a9b8149b3efe3798a57b42); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc51112e1d1a9b8149b3efe3798a57b42)): ?>
<?php $component = $__componentOriginalc51112e1d1a9b8149b3efe3798a57b42; ?>
<?php unset($__componentOriginalc51112e1d1a9b8149b3efe3798a57b42); ?>
<?php endif; ?>
<script>
const shippingRateStoreUrl = "<?php echo e(route('admin.shipping-rates.store')); ?>";
const shippingRateModalEl  = document.getElementById('shippingRateModal');
const cityWrap  = document.getElementById('shippingRateCityWrap');
const cityInput = document.getElementById('shippingRateCity');
let shippingRateModal;

// Choosing a country loads that country's cities into the city field's suggestions
const rateLocation = LocationPicker.bind(
    document.getElementById('shippingRateCountry'),
    cityInput,
    document.getElementById('shippingRateCityOther'),
);

/* A city is only asked for when the rate is for one city; a disabled field isn't sent, so the rate is country-wide. */
function setScope(scope) {
    const forCity = scope === 'city';
    document.getElementById('shippingRateScopeCity').checked    = forCity;
    document.getElementById('shippingRateScopeCountry').checked = !forCity;
    cityWrap.classList.toggle('d-none', !forCity);
    cityInput.disabled = !forCity;
    cityInput.required = forCity;
}

document.querySelectorAll('input[name="scope"]').forEach(radio => radio.addEventListener('change', () => setScope(radio.value)));

/* Free shipping needs no charge: the amount is switched off (so it isn't sent) and saved as 0. */
const amountInput = document.getElementById('shippingRateAmount');
const freeInput   = document.getElementById('shippingRateFree');

function setFree(free) {
    freeInput.checked    = free;
    amountInput.disabled = free;
    amountInput.required = !free;
    if (free) amountInput.value = '';
}

freeInput.addEventListener('change', () => setFree(freeInput.checked));

/* Fill the form: from a saved rate (edit), a rejected attempt, or empty (add). */
function fillShippingRateForm(values, editingId) {
    const form = document.getElementById('shippingRateForm');

    document.getElementById('shippingRateModalTitle').textContent = editingId ? 'Edit Shipping Rate' : 'Add Shipping Rate';
    document.getElementById('shippingRateFormMethod').value = editingId ? 'PUT' : 'POST';
    document.getElementById('shippingRateEditingId').value  = editingId || '';
    form.action = editingId ? shippingRateStoreUrl + '/' + editingId : shippingRateStoreUrl;

    setScope(values.city ? 'city' : 'country');
    rateLocation.setLocation(values.country, values.city);
    // a saved rate of 0 is a free-shipping rate
    setFree(values.free_shipping ?? (values.amount != null && values.amount !== '' && Number(values.amount) === 0));
    if (!freeInput.checked) amountInput.value = values.amount ?? '';
    document.getElementById('shippingRateIsActive').checked = values.is_active ?? true;
}

function openShippingRateModal(rate) {
    fillShippingRateForm(rate || {}, rate ? rate.id : null);
    shippingRateModal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    shippingRateModal = new bootstrap.Modal(shippingRateModalEl);

    <?php if($errors->any()): ?>
        // Save was rejected: reopen the form as the admin left it, still editing the same rate
        fillShippingRateForm({
            country:   <?php echo json_encode(old('country'), 15, 512) ?>,
            city:      <?php echo json_encode(old('city'), 15, 512) ?>,
            amount:    <?php echo json_encode(old('amount'), 15, 512) ?>,
            free_shipping: <?php echo json_encode((bool) old('free_shipping'), 15, 512) ?>,
            is_active: <?php echo json_encode((bool) old('is_active'), 15, 512) ?>,
        }, <?php echo json_encode(old('editing_id'), 15, 512) ?>);
        shippingRateModal.show();
    <?php else: ?>
        setScope('country');
    <?php endif; ?>
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/shipping-rates/index.blade.php ENDPATH**/ ?>