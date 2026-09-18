<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.js"></script>
<script>
    window.showToast = function (message, type = 'success') {
        if (!message || typeof Toastify === 'undefined') return;

        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6',
        };

        Toastify({
            text: message,
            duration: 4000,
            close: true,
            gravity: 'top',
            position: 'right',
            stopOnFocus: true,
            style: { background: colors[type] || colors.success },
        }).showToast();
    };

    document.addEventListener('DOMContentLoaded', function () {
        <?php if(session('status')): ?>
            showToast(<?php echo json_encode(session('status'), 15, 512) ?>, 'success');
        <?php endif; ?>

        <?php if(session('error')): ?>
            showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                showToast(<?php echo json_encode($error, 15, 512) ?>, 'error');
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    });
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/toastify.blade.php ENDPATH**/ ?>