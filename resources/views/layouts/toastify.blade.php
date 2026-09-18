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
        @if (session('status'))
            showToast(@json(session('status')), 'success');
        @endif

        @if (session('error'))
            showToast(@json(session('error')), 'error');
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                showToast(@json($error), 'error');
            @endforeach
        @endif
    });
</script>
