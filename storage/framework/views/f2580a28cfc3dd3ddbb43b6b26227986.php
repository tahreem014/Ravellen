<?php $__env->startSection('title', 'Shipping Rates'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div></div>
        <button class="btn btn-admin-primary btn-sm admin-toolbar-btn" onclick="openShippingRateModal(null)">
            <i class="ri-add-line"></i> Add Shipping Rate
        </button>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>City</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $shippingRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="stack-title"><?php echo e($rate->country); ?></td>
                            <td data-label="City"><?php echo e($rate->city ?? 'All cities (country-wide)'); ?></td>
                            <td data-label="Amount">PKR <?php echo e(number_format($rate->amount, 2)); ?></td>
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
                        <tr><td colspan="5" class="text-center text-muted py-4">No shipping rates found.</td></tr>
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="shippingRateModalTitle">Add Shipping Rate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger small"><?php echo e($errors->first()); ?></div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="shippingRateCountry" class="form-control" value="<?php echo e(old('country')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="shippingRateCity" class="form-control" value="<?php echo e(old('city')); ?>" placeholder="Leave blank for a country-wide rate">
                            <div class="form-text">Leave blank to apply this rate to every city in the country not covered by a more specific rate.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" step="0.01" min="0" name="amount" id="shippingRateAmount" class="form-control" value="<?php echo e(old('amount')); ?>" required>
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
<script>
const shippingRateStoreUrl = "<?php echo e(route('admin.shipping-rates.store')); ?>";
const shippingRateModalEl = document.getElementById('shippingRateModal');
let shippingRateModal;
document.addEventListener('DOMContentLoaded', () => {
    shippingRateModal = new bootstrap.Modal(shippingRateModalEl);
    <?php if($errors->any()): ?>
        shippingRateModal.show();
    <?php endif; ?>
});

function openShippingRateModal(rate) {
    const form = document.getElementById('shippingRateForm');

    if (rate) {
        document.getElementById('shippingRateModalTitle').textContent = 'Edit Shipping Rate';
        document.getElementById('shippingRateFormMethod').value = 'PUT';
        form.action = shippingRateStoreUrl + '/' + rate.id;
        document.getElementById('shippingRateCountry').value = rate.country || '';
        document.getElementById('shippingRateCity').value = rate.city || '';
        document.getElementById('shippingRateAmount').value = rate.amount || '';
        document.getElementById('shippingRateIsActive').checked = !!rate.is_active;
    } else {
        document.getElementById('shippingRateModalTitle').textContent = 'Add Shipping Rate';
        document.getElementById('shippingRateFormMethod').value = 'POST';
        form.action = shippingRateStoreUrl;
        form.reset();
        document.getElementById('shippingRateIsActive').checked = true;
    }

    shippingRateModal.show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/shipping-rates/index.blade.php ENDPATH**/ ?>