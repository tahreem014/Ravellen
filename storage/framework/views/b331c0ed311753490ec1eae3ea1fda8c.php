<?php $__env->startSection('title', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
    <div class="admin-card card" style="max-width:640px;">
        <div class="card-body">
            <h5 class="mb-3">Store Settings</h5>
            <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                <div class="mb-3">
                    <label class="form-label">Support Email</label>
                    <input type="email" name="support_email" value="<?php echo e(old('support_email', $settings['support_email'])); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Support Phone</label>
                    <input type="text" name="support_phone" value="<?php echo e(old('support_phone', $settings['support_phone'])); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pre-order Advance Rate (0–1)</label>
                    <input type="number" step="0.01" min="0" max="1" name="preorder_advance_rate" value="<?php echo e(old('preorder_advance_rate', $settings['preorder_advance_rate'])); ?>" class="form-control" required>
                    <div class="form-text">e.g. 0.4 = 40% advance, 60% collected once the item is back in stock.</div>
                </div>

                <div class="alert alert-info small">
                    This value is stored for reference and future use. The live checkout still uses the advance-rate constant in code (<code>OrderService</code>) — updating it here does not change checkout behavior yet.
                </div>

                <button type="submit" class="btn btn-admin-primary">Save Settings</button>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/settings/edit.blade.php ENDPATH**/ ?>