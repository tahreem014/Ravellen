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

    /* Read a fetch() response as JSON without throwing: a throttle page, an
       expired session (419) or a server error come back as HTML, not JSON. */
    window.readJson = async function (res) {
        try { return await res.json(); } catch (e) { return {}; }
    };

    /* A customer-friendly message for a failed request, whatever the status. */
    window.requestErrorMessage = function (res, data, fallback) {
        if (res.status === 429) {
            const wait = parseInt(res.headers.get('Retry-After'), 10);
            return 'Too many attempts. Please wait ' + (wait > 0 ? wait + ' seconds' : 'a minute') + ' and try again.';
        }
        if (res.status === 419) return 'Your session expired. Please refresh the page and try again.';
        if (res.status === 401) return 'Please sign in again to continue.';

        // Our own controllers answer with { success: false, message } on purpose; a bare 5xx
        // message is framework noise ("Server Error", an exception text) a customer should not see.
        if (data && data.message && (res.status < 500 || data.success === false)) return data.message;

        return fallback;
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
