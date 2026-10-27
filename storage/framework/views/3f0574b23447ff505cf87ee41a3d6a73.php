

<?php $__env->startSection('title', 'Order #' . $order->id); ?>

<?php $__env->startSection('content'); ?>
    <div class="mb-3">
        <a href="<?php echo e($order->isPreOrder() ? route('admin.pre-orders.index') : route('admin.orders.index')); ?>" class="text-decoration-none">
            <i class="ri-arrow-left-line"></i> Back to <?php echo e($order->isPreOrder() ? 'Pre-Orders' : 'Orders'); ?>

        </a>
    </div>

    
    <div class="row g-4" data-admin-region="order">
        <div class="col-lg-8">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Items</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($item->product?->name); ?></td>
                                        <td><?php echo e($item->productVariant?->variant_name); ?></td>
                                        <td><?php echo e($item->qty); ?></td>
                                        <td>PKR <?php echo e(number_format($item->price)); ?></td>
                                        <td>PKR <?php echo e(number_format($item->price * $item->qty)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="mb-1">Update Status</h5>
                    <p class="text-muted small mb-3">The customer sees the new status in their account (Order Details, My Orders, Track Order). No email is sent.</p>
                    <form action="<?php echo e(route('admin.orders.update', $order)); ?>" method="POST" class="row g-3" data-ajax>
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <div class="col-md-6">
                            <label class="form-label">Order Status</label>
                            <select name="order_status" class="form-select">
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>" <?php if($order->order_status === $status): echo 'selected'; endif; ?>><?php echo e(\App\Models\Order::STATUS_LABELS[$status]); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order Reference</label>
                            <div class="form-control-plaintext"><?php echo e($order->tracking_number ?? '—'); ?></div>
                            <div class="form-text">Customers can search this on Track Order.</div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-admin-primary" type="submit">Save</button>
                        </div>
                    </form>
                    <?php if($order->delivered_at): ?>
                        <p class="text-muted small mt-3 mb-0">Delivered <?php echo e($order->delivered_at->format('d M Y, g:i a')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Customer</h5>
                    <p class="mb-1"><?php echo e($order->user?->name); ?></p>
                    <p class="mb-1 text-muted"><?php echo e($order->user?->email); ?></p>
                    <p class="mb-0 text-muted"><?php echo e($order->shipping_phone); ?></p>
                </div>
            </div>

            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Shipping</h5>
                    <p class="mb-1"><?php echo e($order->shipping_name); ?></p>
                    <p class="mb-0 text-muted"><?php echo e($order->shipping_address); ?></p>
                </div>
            </div>

            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="mb-3">Payment</h5>
                    <p class="mb-1">Method: <?php echo e($order->payment_method_label); ?></p>
                    <p class="mb-1">Status: <?php echo e(ucfirst(str_replace('_', ' ', $order->payment_status))); ?></p>
                    <?php if($order->payment_reference): ?>
                        <p class="mb-1 text-muted small">Safepay ref: <?php echo e($order->payment_reference); ?></p>
                    <?php endif; ?>
                    <?php if($order->paid_at): ?>
                        <p class="mb-1 text-muted small">Paid: <?php echo e($order->paid_at->format('d M Y H:i')); ?></p>
                    <?php endif; ?>
                    <?php if($order->isPreOrder()): ?>
                        <hr class="my-2">
                        <p class="mb-1">Advance: PKR <?php echo e(number_format($order->advance_amount)); ?>

                            <?php if($order->payment_stage === \App\Models\Order::STAGE_ADVANCE_PAID || $order->payment_stage === \App\Models\Order::STAGE_COMPLETED): ?>
                                <span class="badge bg-success">paid</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">pending</span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-1">Remaining: PKR <?php echo e(number_format($order->remaining_amount)); ?>

                            <?php if($order->payment_stage === \App\Models\Order::STAGE_COMPLETED): ?>
                                <span class="badge bg-success">paid</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">pending</span>
                            <?php endif; ?>
                        </p>
                        <?php if($order->remaining_payment_reference): ?>
                            <p class="mb-1 text-muted small">Remaining Safepay ref: <?php echo e($order->remaining_payment_reference); ?></p>
                        <?php endif; ?>
                        <?php if($order->remaining_paid_at): ?>
                            <p class="mb-1 text-muted small">Remaining paid: <?php echo e($order->remaining_paid_at->format('d M Y H:i')); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="mb-0 fw-bold mt-2">Total: PKR <?php echo e(number_format($order->total_amount)); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>