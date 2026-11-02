
<script>
(function () {
    const TRACK_URL = '<?php echo e(route('track.click', [], false)); ?>';
    const CLICKABLE = 'a[href], button, input[type="submit"], input[type="button"], [role="button"]';

    document.addEventListener('click', function (e) {
        if (!e.isTrusted || !navigator.sendBeacon) return;

        const el = e.target.closest && e.target.closest(CLICKABLE);
        if (!el || el.disabled) return;

        navigator.sendBeacon(TRACK_URL);
    }, true);
})();
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/track-clicks.blade.php ENDPATH**/ ?>