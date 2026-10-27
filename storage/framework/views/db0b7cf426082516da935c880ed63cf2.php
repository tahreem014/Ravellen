

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
                        <th>Date</th>
                        <th>Total (PKR)</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="stack-title"><?php echo e($order->tracking_number ?? '—'); ?></td>
                            <td data-label="Customer"><?php echo e($order->user?->name); ?></td>
                            <td data-label="Date"><?php echo e($order->order_date?->format('M j, Y')); ?></td>
                            <td data-label="Total (PKR)"><?php echo e(number_format($order->total_amount)); ?></td>
                            <td data-label="Payment"><span class="badge bg-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warning'); ?>"><?php echo e(ucfirst($order->payment_status)); ?></span></td>
                            <td data-label="Status"><span class="badge bg-secondary"><?php echo e($order->status_label); ?></span></td>
                            <td class="text-end stack-actions">
                                <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="btn btn-sm btn-outline-secondary"><i class="ri-eye-line"></i> View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($orders->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>