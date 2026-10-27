
<?php $__env->startSection('title', $order->tracking_number); ?>

<?php $__env->startSection('css'); ?>
<style>
    .order-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    .order-card {
        background: #fff; border: 0; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07); padding: 1.75rem;
    }

    .section-title {
        font-size: .95rem; font-weight: 700; color: #1f2937;
        display: flex; align-items: center; gap: .5rem; margin-bottom: 1.25rem;
    }
    .section-title i { color: #a855f7; font-size: 1.1rem; }

    .status-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .78rem; font-weight: 700; padding: .3rem .75rem;
        border-radius: 2rem;
    }
    .status-pending, .status-awaiting_stock  { background: #fef3c7; color: #92400e; }
    .status-payment_due                       { background: #fee2e2; color: #991b1b; }
    .status-confirmed                         { background: #d1fae5; color: #065f46; }
    .status-processing                        { background: #dbeafe; color: #1e40af; }
    .status-shipped                           { background: #e0e7ff; color: #3730a3; }
    .status-out_for_delivery                  { background: #fce7f3; color: #9d174d; }
    .status-delivered                         { background: #dcfce7; color: #166534; }
    .status-cancelled                         { background: #f3f4f6; color: #4b5563; }

    /* ── Delivery progress (Order::trackingSteps): a row of steps, a column on phones ── */
    .track-steps { display: flex; }
    .track-step {
        flex: 1 1 0; position: relative; display: flex; flex-direction: column;
        align-items: center; text-align: center; gap: .45rem; padding: 0 .2rem;
    }
    .track-step + .track-step::before {
        content: ''; position: absolute; top: .5rem; height: 3px;
        left: calc(-50% + .6rem); right: calc(50% + .6rem); background: #e5e7eb;
    }
    .track-step.done + .track-step::before { background: #a855f7; }
    .track-dot {
        width: 1.15rem; height: 1.15rem; border-radius: 50%; flex-shrink: 0;
        border: 3px solid #e5e7eb; background: #fff; position: relative; z-index: 1;
    }
    .track-step.done .track-dot      { background: #a855f7; border-color: #a855f7; }
    .track-step.active .track-dot    { background: #ec4899; border-color: #ec4899; box-shadow: 0 0 0 4px rgba(236,72,153,.2); }
    .track-step.cancelled .track-dot { background: #6b7280; border-color: #6b7280; }
    .track-label { font-size: .78rem; font-weight: 600; color: #1f2937; line-height: 1.25; }
    .track-step.pending .track-label { color: #9ca3af; }
    .track-step.active .track-label  { color: #db2777; }
    .track-date  { font-size: .7rem; color: #9ca3af; }

    @media (max-width: 767.98px) {
        .track-steps { flex-direction: column; }
        .track-step { flex-direction: row; align-items: flex-start; text-align: left; gap: .75rem; padding: 0 0 1.1rem; }
        .track-step + .track-step::before { display: none; }
        .track-step:not(:last-child)::after {
            content: ''; position: absolute; left: .5rem; top: 1.25rem; bottom: .1rem;
            width: 3px; background: #e5e7eb;
        }
        .track-step.done:not(:last-child)::after { background: #a855f7; }
    }

    .order-item-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; padding: .9rem 0; border-bottom: 1px solid #f3f4f6;
    }
    .order-item-row:last-child { border-bottom: none; }
    .order-item-name { font-weight: 600; font-size: .9rem; color: #1f2937; }
    .order-item-meta { font-size: .78rem; color: #6b7280; }
    .order-item-price { font-weight: 700; font-size: .9rem; color: #1f2937; white-space: nowrap; }

    .money-row { display: flex; justify-content: space-between; font-size: .875rem; padding: .4rem 0; }
    .money-row.total { font-weight: 700; font-size: 1.05rem; padding-top: .8rem; border-top: 1px solid #f3f4f6; margin-top: .4rem; }

    .preorder-note {
        background: #fffbeb; border: 1px solid #fcd34d; border-radius: .6rem;
        padding: .85rem 1rem; font-size: .85rem; color: #92400e; margin-top: 1rem;
    }

    .pay-remaining-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 700; font-size: .95rem;
        padding: .65rem 1rem; width: 100%; margin-top: 1rem;
        transition: opacity .2s;
    }
    .pay-remaining-btn:hover { opacity: .88; color: #fff; }
    .pay-remaining-btn:disabled { opacity: .6; cursor: not-allowed; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="order-wrapper py-5">

<div class="container px-4" style="max-width: 900px;" data-live-region="order-detail">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="fw-bold mb-1" style="font-size:1.75rem;background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                <?php echo e($order->tracking_number); ?>

            </h1>
            <p class="text-muted small mb-0">Placed on <?php echo e($order->order_date?->format('d M Y') ?? $order->created_at->format('d M Y')); ?></p>
        </div>
        <span class="status-pill status-<?php echo e($order->order_status); ?>"><?php echo e($order->status_label); ?></span>
    </div>

    
    <div class="order-card mb-4">
        <div class="section-title"><i class="ri-truck-line"></i> Delivery Status</div>
        <div class="track-steps">
            <?php $__currentLoopData = $order->trackingSteps(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="track-step <?php echo e($step['state']); ?>">
                <span class="track-dot"></span>
                <div>
                    <div class="track-label"><?php echo e($step['label']); ?></div>
                    <div class="track-date"><?php echo e($step['date']); ?></div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <?php if($order->order_type === 'pre_order' && $order->order_status === 'payment_due'): ?>
    <div class="preorder-note d-flex align-items-start gap-2 mb-4">
        <i class="ri-information-line mt-1"></i>
        <div>Your preorder item is now available. Please complete the remaining payment below to confirm your order.</div>
    </div>
    <?php elseif($order->order_type === 'pre_order' && $order->order_status === 'awaiting_stock'): ?>
    <div class="preorder-note d-flex align-items-start gap-2 mb-4">
        <i class="ri-time-line mt-1"></i>
        <div>This is a preorder. You've paid the 40% advance — we'll email and notify you here as soon as the item is back in stock so you can pay the remaining balance.</div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="order-card mb-4">
                <div class="section-title"><i class="ri-shopping-bag-3-line"></i> Items</div>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="order-item-row">
                    <div>
                        <div class="order-item-name"><?php echo e($item->product?->name ?? 'Product'); ?></div>
                        <div class="order-item-meta">
                            <?php echo e($item->productVariant?->variant_name ? 'Variant: '.$item->productVariant->variant_name.' · ' : ''); ?>Qty: <?php echo e($item->qty); ?>

                            <?php if($item->comment): ?>
                                <br><span style="font-style:italic;">"<?php echo e($item->comment); ?>"</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="order-item-price">PKR <?php echo e(number_format($item->price * $item->qty)); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="order-card">
                <div class="section-title"><i class="ri-map-pin-line"></i> Shipping</div>
                <p class="mb-1 fw-semibold small"><?php echo e($order->shipping_name); ?> &middot; <?php echo e($order->shipping_phone); ?></p>
                <p class="text-muted small mb-0"><?php echo e($order->shipping_address); ?></p>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="order-card">
                <div class="section-title"><i class="ri-bank-card-line"></i> Payment</div>

                <div class="money-row"><span class="text-muted">Payment method</span><span><?php echo e($order->payment_method_label); ?></span></div>
                <div class="money-row"><span class="text-muted">Shipping fee</span><span>PKR <?php echo e(number_format($order->shipping_fee)); ?></span></div>

                <?php if($order->order_type === 'pre_order'): ?>
                    <div class="money-row"><span class="text-muted">Advance (40%)</span><span>PKR <?php echo e(number_format($order->advance_amount)); ?></span></div>
                    <div class="money-row">
                        <span class="text-muted">Remaining balance</span>
                        <span>PKR <?php echo e(number_format($order->remaining_amount)); ?></span>
                    </div>
                    <div class="money-row total"><span>Order total</span><span>PKR <?php echo e(number_format($order->total_amount)); ?></span></div>

                    <?php if($order->isAwaitingInitialPayment()): ?>
                        <button class="pay-remaining-btn" id="pay-now-btn" onclick="payNow()">
                            Pay Advance — PKR <?php echo e(number_format($order->advance_amount)); ?>

                        </button>
                    <?php elseif($order->order_status === 'payment_due'): ?>
                        <button class="pay-remaining-btn" id="pay-remaining-btn" onclick="payRemaining()">
                            Pay Remaining — PKR <?php echo e(number_format($order->remaining_amount)); ?>

                        </button>
                    <?php elseif($order->payment_stage === 'completed'): ?>
                        <p class="text-muted small mt-3 mb-0"><i class="ri-checkbox-circle-line" style="color:#22c55e;"></i> Remaining balance paid <?php echo e($order->remaining_paid_at?->format('d M Y')); ?>.</p>
                    <?php elseif($order->payment_stage === 'advance_paid'): ?>
                        <p class="text-muted small mt-3 mb-0"><i class="ri-checkbox-circle-line" style="color:#22c55e;"></i> Advance paid<?php echo e($order->paid_at ? ' '.$order->paid_at->format('d M Y') : ''); ?>.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if($order->isAwaitingInitialPayment()): ?>
                        <button class="pay-remaining-btn" id="pay-now-btn" onclick="payNow()">
                            Pay Now — PKR <?php echo e(number_format($order->total_amount)); ?>

                        </button>
                    <?php else: ?>
                        <div class="money-row total"><span>Total paid</span><span>PKR <?php echo e(number_format($order->total_amount)); ?></span></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
const CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';
const ORDER_UUID = <?php echo json_encode($order->uuid, 15, 512) ?>;

async function startSafepayPayment(url, btn, originalLabel) {
    btn.disabled = true;
    btn.textContent = 'Redirecting to payment…';

    try {
        const res  = await fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok || !data.redirect) {
            showToast(data.message || 'Payment failed. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = originalLabel;
            return;
        }

        window.location.href = data.redirect;
    } catch (e) {
        console.error('Payment start failed:', e);
        showToast('Something went wrong. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = originalLabel;
    }
}

function payNow() {
    const btn = document.getElementById('pay-now-btn');
    startSafepayPayment(`/orders/${ORDER_UUID}/pay`, btn, btn.textContent);
}

function payRemaining() {
    const btn = document.getElementById('pay-remaining-btn');
    startSafepayPayment(`/orders/${ORDER_UUID}/pay-remaining`, btn, btn.textContent);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/order-detail.blade.php ENDPATH**/ ?>