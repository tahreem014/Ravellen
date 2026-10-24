
<script>
(function () {
    const LIVE_URL = '<?php echo e(route('live', [], false)); ?>';
    const POLL_MS  = 5000;
    let version = <?php echo json_encode(app(\App\Services\LiveUpdateService::class)->version(), 15, 512) ?>;
    let unread  = <?php echo json_encode(auth()->check() ? auth()->user()->unreadNotifications()->count() : null, 15, 512) ?>;
    let ordersVersion = <?php echo json_encode(auth()->check() ? app(\App\Services\LiveUpdateService::class)->ordersVersion(auth()->id()) : null, 15, 512) ?>;
    let regionsStale  = false;
    let polling = false;

    const pkr = (n) => 'PKR ' + Math.round(n).toLocaleString();
    /* Same markup as the components/price.blade.php component. */
    window.livePriceHtml = (amount, original, prefix = '') => prefix + pkr(amount) + (original && original > amount
        ? ' <s class="price-was" style="color:#9ca3af;-webkit-text-fill-color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">' + pkr(original) + '</s>'
        : '');

    function pageWants() {
        const products = new Set((window.LIVE_PRODUCT_IDS || []).map(String));
        document.querySelectorAll('[data-live-product]').forEach(el => products.add(el.dataset.liveProduct));
        const categories = [...new Set([...document.querySelectorAll('[data-live-category]')].map(el => el.dataset.liveCategory))];
        const listings = [...new Set([...document.querySelectorAll('[data-live-listing]')].map(el => el.dataset.liveListing))];
        return { products: [...products], categories, listings };
    }

    function setBadge(el, label) {
        el.textContent = label || '';
        el.style.display = label ? '' : 'none';
    }

    function patchCards(data) {
        document.querySelectorAll('[data-live-product]').forEach(card => {
            const product = data.products[card.dataset.liveProduct];
            if (!product) { card.remove(); return; }   // deleted by the admin

            card.querySelectorAll('[data-live-name]').forEach(el => el.textContent = product.name);
            card.querySelectorAll('[data-live-price]').forEach(el => {
                if (product.from) el.innerHTML = livePriceHtml(product.from.price, product.from.original, el.dataset.livePrefix || '');
            });
            card.querySelectorAll('[data-live-badge]').forEach(el => setBadge(el, product.badge));
            card.querySelectorAll('img[data-live-image]').forEach(img => {
                if (product.image && img.getAttribute('src') !== product.image) img.src = product.image;
            });
        });

        document.querySelectorAll('[data-live-category]').forEach(card => {
            const from = data.categories[card.dataset.liveCategory];
            if (!from) return;
            card.querySelectorAll('[data-live-price]').forEach(el => el.innerHTML = livePriceHtml(from.price, from.original, el.dataset.livePrefix || ''));
        });
    }

    /**
     * Re-render the page's [data-live-region] sections from a fresh copy of
     * the page. Waits (stays stale, retried next tick) while a dialog is open
     * or the shopper is typing inside one of those sections.
     */
    async function refreshRegions() {
        const regions = [...document.querySelectorAll('[data-live-region]')];
        if (!regions.length) { regionsStale = false; return; }

        const active = document.activeElement;
        const typing = active && active.matches('input, textarea, select') && regions.some(region => region.contains(active));
        if (typing || document.querySelector('.modal.show')) return;

        const res = await fetch(location.href, { headers: { 'Accept': 'text/html', 'X-Live-Refresh': '1' }, cache: 'no-store' });
        if (!res.ok) return;
        const fresh = new DOMParser().parseFromString(await res.text(), 'text/html');

        regions.forEach(region => {
            const next = fresh.querySelector(`[data-live-region="${region.dataset.liveRegion}"]`);
            if (next && next.innerHTML !== region.innerHTML) region.innerHTML = next.innerHTML;
        });
        regionsStale = false;
    }

    async function poll() {
        if (polling || document.visibilityState !== 'visible') return;
        polling = true;

        try {
            const wants  = pageWants();
            const params = new URLSearchParams({ v: version });
            wants.products.forEach(id => params.append('products[]', id));
            wants.categories.forEach(id => params.append('categories[]', id));
            wants.listings.forEach(key => params.append('listings[]', key));

            const res = await fetch(LIVE_URL + '?' + params, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();

            if (data.unread !== undefined) {
                if (unread !== null && data.unread !== unread) {
                    document.dispatchEvent(new CustomEvent('live:notifications', { detail: { unread: data.unread, increased: data.unread > unread } }));
                }
                unread = data.unread;
            }

            if (data.orders_version !== undefined && data.orders_version !== ordersVersion) {
                ordersVersion = data.orders_version;
                regionsStale = true;
                document.dispatchEvent(new CustomEvent('live:orders'));
            }
            if (regionsStale) await refreshRegions();

            if (!data.changed) return;
            version = data.version;

            // a listing that gained or lost products is rebuilt from the server
            for (const el of document.querySelectorAll('[data-live-listing]')) {
                const signature = data.listings[el.dataset.liveListing];
                if (signature && signature !== el.dataset.liveSignature) {
                    location.reload();
                    return;
                }
            }

            patchCards(data);
            document.dispatchEvent(new CustomEvent('live:catalog', { detail: data }));
        } catch (e) {
            // offline or server busy — try again on the next tick
        } finally {
            polling = false;
        }
    }

    setInterval(poll, POLL_MS);
    document.addEventListener('visibilitychange', poll);
})();
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/live-updates.blade.php ENDPATH**/ ?>