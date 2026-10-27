

<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0"><?php echo e($title); ?></h5>
        <form method="GET" class="admin-toolbar-form">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Order::STATUS_LABELS[$status]); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Customer</th>
                        <th>Advance</th>
                        <th>Remaining</th>
                        <th>Stage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="stack-title"><?php echo e($order->tracking_number ?? '—'); ?></td>
                            <td data-label="Customer"><?php echo e($order->user?->name); ?></td>
                            <td data-label="Advance">PKR <?php echo e(number_format($order->advance_amount)); ?></td>
                            <td data-label="Remaining">PKR <?php echo e(number_format($order->remaining_amount)); ?></td>
                            <td data-label="Stage"><?php echo e($order->payment_stage ? ucfirst(str_replace('_',' ', $order->payment_stage)) : '—'); ?></td>
                            <td data-label="Status"><span class="badge bg-secondary"><?php echo e($order->status_label); ?></span></td>
                            <td class="text-end stack-actions">
                                <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="btn btn-sm btn-outline-secondary"><i class="ri-eye-line"></i> View</a>
                                <?php if($order->order_status === \App\Models\Order::STATUS_PAYMENT_DUE): ?>
                                    <form action="<?php echo e(route('admin.pre-orders.collect-remaining', $order)); ?>" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Mark the remaining payment as collected?', 'btn-admin-primary');">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-sm btn-admin-primary">Collect Remaining</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No pre-orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($orders->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/orders/pre-orders.blade.php ENDPATH**/ ?>