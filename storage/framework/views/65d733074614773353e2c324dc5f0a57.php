<?php $__env->startSection('css'); ?>
<style>
    .notif-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    .notif-card {
        background: #fff; border: 0; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07); overflow: hidden;
    }

    .notif-row {
        display: flex; align-items: flex-start; gap: .85rem;
        padding: 1.1rem 1.25rem; border-bottom: 1px solid #f3f4f6;
        text-decoration: none; color: inherit;
    }
    .notif-row:last-child { border-bottom: none; }
    .notif-row:hover { background: #fdf2f8; color: inherit; text-decoration: none; }
    .notif-row.unread { background: #faf5ff; }

    .notif-dot {
        width: .55rem; height: .55rem; border-radius: 50%;
        background: #a855f7; margin-top: .4rem; flex-shrink: 0;
    }
    .notif-row.read .notif-dot { background: transparent; }

    .notif-message { font-size: .9rem; color: #1f2937; margin-bottom: .2rem; }
    .notif-time { font-size: .75rem; color: #9ca3af; }

    .notif-empty { padding: 3rem 1.5rem; text-align: center; color: #9ca3af; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="notif-wrapper py-5">
<div class="container px-4" style="max-width: 720px;">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="fw-bold mb-0" style="font-size:1.75rem;background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Notifications
        </h1>
        <?php if($notifications->isNotEmpty()): ?>
        <button class="btn btn-outline-secondary btn-sm" onclick="markAllAsRead()">Mark all as read</button>
        <?php endif; ?>
    </div>

    <div class="notif-card">
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="#"
               class="notif-row <?php echo e($notification->read_at ? 'read' : 'unread'); ?>"
               data-id="<?php echo e($notification->id); ?>"
               onclick="openNotification(event, '<?php echo e($notification->id); ?>', <?php echo e(Js::from($notification->data['action_url'] ?? null)); ?>)">
                <span class="notif-dot"></span>
                <div>
                    <div class="notif-message"><?php echo e($notification->data['message'] ?? ''); ?></div>
                    <div class="notif-time"><?php echo e($notification->created_at->diffForHumans()); ?></div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="notif-empty">
                <i class="ri-notification-off-line" style="font-size:2rem;"></i>
                <p class="mb-0 mt-2">You don't have any notifications yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if($notifications->hasPages()): ?>
    <div class="mt-4"><?php echo e($notifications->links()); ?></div>
    <?php endif; ?>

</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
const CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';

async function openNotification(event, id, actionUrl) {
    event.preventDefault();

    try {
        await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
    } catch (e) {}

    if (actionUrl) {
        window.location.href = actionUrl;
    } else {
        window.location.reload();
    }
}

async function markAllAsRead() {
    try {
        await fetch('/notifications/read-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
    } catch (e) {}

    window.location.reload();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/notifications.blade.php ENDPATH**/ ?>