{{--
    Click tracking for the admin dashboard's "Total Clicks": beacons POST /track/click
    whenever a shopper presses a link or button. The server counts it only inside a
    visit it already recorded (see App\Http\Middleware\RecordVisit).
--}}
<script>
(function () {
    const TRACK_URL = '{{ route('track.click', [], false) }}';
    const CLICKABLE = 'a[href], button, input[type="submit"], input[type="button"], [role="button"]';

    document.addEventListener('click', function (e) {
        if (!e.isTrusted || !navigator.sendBeacon) return;

        const el = e.target.closest && e.target.closest(CLICKABLE);
        if (!el || el.disabled) return;

        navigator.sendBeacon(TRACK_URL);
    }, true);
})();
</script>
