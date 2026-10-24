<?php $__env->startSection('title', $title); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0"><?php echo e($title); ?></h5>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e(ucfirst($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
    </div>

    
    <div class="admin-card card" data-admin-region="refunds">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th>Amount (PKR)</th>
                        <th>Bank Details</th>
                        <th>Reason</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $refundRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($refund->id); ?></td>
                            <td><?php echo e($refund->orderItem?->order?->user?->name ?? '—'); ?></td>
                            <td><?php echo e($refund->orderItem?->product?->name ?? '—'); ?></td>
                            <td><?php echo e(number_format($refund->amount)); ?></td>
                            <td><?php echo e($refund->bank_details); ?></td>
                            <td><?php echo e($refund->reason); ?></td>
                            <td><?php echo e($refund->requested_at?->format('M j, Y')); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e(match($refund->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'warning',
                                }); ?>"><?php echo e(ucfirst($refund->status)); ?></span>
                            </td>
                            <td class="text-end">
                                <?php if($refund->status === 'pending'): ?>
                                    <form action="<?php echo e(route('admin.refund-requests.update', $refund)); ?>" method="POST" class="d-inline" data-ajax>
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-outline-success"><i class="ri-check-line"></i> Approve</button>
                                    </form>
                                    <form action="<?php echo e(route('admin.refund-requests.update', $refund)); ?>" method="POST" class="d-inline" data-ajax>
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PUT'); ?>
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-sm btn-outline-danger"><i class="ri-close-line"></i> Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No refund requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($refundRequests->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/refund-requests/index.blade.php ENDPATH**/ ?>