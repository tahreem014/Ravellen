@extends('layouts.landing-page-layout')
@section('title', 'Checkout')

@section('css')
<style>
    .checkout-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    .checkout-card {
        background: #fff; border: 0; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07); padding: 1.75rem;
    }

    .section-title {
        font-size: .95rem; font-weight: 700; color: #1f2937;
        display: flex; align-items: center; gap: .5rem; margin-bottom: 1.25rem;
    }
    .section-title i { color: #a855f7; font-size: 1.1rem; }

    .form-label-sm { font-size: .8rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }

    .form-control-custom {
        border: 1.5px solid #e5e7eb; border-radius: .6rem;
        padding: .65rem .9rem; font-size: .875rem; width: 100%;
        outline: none; transition: border-color .2s, box-shadow .2s;
        background: #fff; color: #1f2937;
    }
    .form-control-custom:focus {
        border-color: #a855f7;
        box-shadow: 0 0 0 3px rgba(168,85,247,.12);
    }
    .form-control-custom::placeholder { color: #9ca3af; }

    .input-icon-wrap { position: relative; }
    .input-icon-wrap .input-icon {
        position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
        color: #9ca3af; font-size: .9rem; pointer-events: none;
    }
    .input-icon-wrap .form-control-custom { padding-left: 2.2rem; }

    /* Payment methods */
    .payment-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
    .payment-option {
        border: 1.5px solid #e5e7eb; border-radius: .65rem;
        padding: .7rem 1rem; cursor: pointer;
        display: flex; align-items: center; gap: .6rem;
        transition: border-color .2s, background .2s;
        font-size: .875rem; font-weight: 500; color: #374151;
        background: #fff;
    }
    .payment-option:hover { border-color: #a855f7; background: #faf5ff; }
    .payment-option.selected { border-color: #a855f7; background: #faf5ff; }
    .payment-option .pay-radio {
        width: .85rem; height: .85rem; border-radius: 50%;
        border: 2px solid #d1d5db; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        transition: border-color .2s;
    }
    .payment-option.selected .pay-radio { border-color: #a855f7; }
    .payment-option.selected .pay-radio::after {
        content: ''; width: .45rem; height: .45rem;
        border-radius: 50%; background: #a855f7; display: block;
    }
    .pay-icon { font-size: 1.1rem; }

    .cod-note {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: .6rem; padding: .7rem 1rem;
        font-size: .8rem; color: #1e40af; margin-top: 1rem;
    }

    .default-address-note {
        background: #faf5ff; border: 1px solid #e9d5ff;
        border-radius: .6rem; padding: .65rem 1rem;
        font-size: .8rem; color: #6b21a8; margin-bottom: 1rem;
        display: flex; align-items: center; gap: .5rem;
    }
    .default-address-note a { color: #7c3aed; font-weight: 600; text-decoration: none; }
    .default-address-note a:hover { text-decoration: underline; }

    /* Order summary */
    .summary-card {
        background: #fff; border: 0; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07); padding: 1.5rem;
    }
    .order-item-img {
        width: 3.5rem; height: 3.5rem; border-radius: .5rem;
        object-fit: cover; background: #f3e8ff; flex-shrink: 0;
    }
    .order-item-img-placeholder {
        width: 3.5rem; height: 3.5rem; border-radius: .5rem;
        background: linear-gradient(135deg,#f3e8ff,#fce7f3); flex-shrink: 0;
    }

    .place-order-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 700; font-size: 1rem;
        padding: .5rem 1rem; width: 100%;
        transition: opacity .2s; letter-spacing: .01em;
    }
    .place-order-btn:hover { opacity: .88; color: #fff; }

    .item-line-price { font-weight: 600; font-size: .875rem; color: #1f2937; }
    .item-comment {
        font-size: .75rem; color: #6b7280; font-style: italic;
        overflow-wrap: break-word;
    }

    /* ── Order Confirmed Overlay ── */
    .confirm-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(245,243,255,.85);
        backdrop-filter: blur(6px);
        display: none; align-items: center; justify-content: center;
        padding: 1rem;
    }
    .confirm-overlay.show { display: flex; }
    .confirm-card {
        background: #fff; border-radius: 1.25rem;
        box-shadow: 0 16px 48px rgba(0,0,0,.13);
        padding: 2.5rem 2rem; max-width: 30rem; width: 100%;
        text-align: center; animation: popIn .35s ease;
    }
    @keyframes popIn {
        from { transform: scale(.88); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
    .confirm-icon {
        width: 4.5rem; height: 4.5rem; border-radius: 50%;
        background: #22c55e;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
    }
    .confirm-icon i { font-size: 2rem; color: #fff; }
    .confirm-title {
        font-size: 1.5rem; font-weight: 800;
        background: linear-gradient(to right, #ec4899, #a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: .5rem;
    }
    .confirm-meta {
        background: #f5f3ff; border-radius: .75rem;
        padding: 1rem 1.25rem; text-align: left;
        display: grid; grid-template-columns: 1fr 1fr; gap: .75rem 1rem;
        margin: 1.25rem 0;
    }
    .confirm-meta-label { font-size: .72rem; color: #6b7280; margin-bottom: .15rem; }
    .confirm-meta-value { font-size: .875rem; font-weight: 700; color: #1f2937; }
    .confirm-meta-value.order-id { color: #a855f7; }
    .confirm-meta-value.status   { color: #22c55e; }
    .track-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .875rem;
        padding: .6rem 1.4rem; transition: opacity .2s;
    }
    .track-btn:hover { opacity: .88; color: #fff; }
    .continue-shop-btn {
        border: 1.5px solid #e5e7eb; border-radius: .5rem;
        background: #fff; color: #374151;
        font-weight: 600; font-size: .875rem;
        padding: .6rem 1.4rem; transition: border-color .2s;
    }
    .continue-shop-btn:hover { border-color: #a855f7; color: #7c3aed; }
</style>
@endsection

@section('content')
<div class="checkout-wrapper py-5">
<div class="container px-4">

    <h1 class="fw-bold mb-1" style="font-size:2rem;background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        Checkout
    </h1>
    <p class="text-muted mb-4 small">Complete your order</p>

    @if($resume ?? null)
        {{-- Just signed in from "Place Order": the saved details are already in the form below --}}
        <div class="default-address-note mb-4" id="resume-banner">
            <i class="ri-shield-check-fill"></i>
            <span id="resume-banner-text">You're signed in. Your details are filled in below.</span>
        </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- ── Left: Forms ── --}}
        <div class="col-lg-8">

            {{-- Shipping Information --}}
            <div class="checkout-card mb-4">
                <div class="section-title">
                    <i class="ri-map-pin-line"></i> Shipping Information
                </div>

                @if(($addresses ?? collect())->isNotEmpty())
                <div class="mb-3">
                    <div class="form-label-sm">Shipping To *</div>
                    <select class="form-control-custom" id="saved-address" onchange="fillFromAddress(this.value)">
                        @foreach($addresses as $addr)
                            <option value="{{ $addr->id }}"
                                data-name="{{ $addr->name }}"
                                data-phone="{{ $addr->phone }}"
                                data-address="{{ $addr->address }}"
                                {{ optional($defaultAddress)->id === $addr->id ? 'selected' : '' }}>
                                {{ $addr->name }} — {{ $addr->address }}{{ $addr->city ? ', ' . $addr->city : '' }}{{ $addr->country ? ', ' . $addr->country : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="default-address-note">
                    <i class="ri-map-pin-2-fill"></i>
                    <span>
                        Choose a saved address above, or <a href="{{ route('profile') }}?tab=addresses">manage addresses</a>.
                    </span>
                </div>
                @else
                <div class="cod-note">
                    <strong>No saved addresses.</strong>
                    <a href="{{ route('profile') }}?tab=addresses">Add a shipping address</a> to continue checkout.
                </div>
                @endif

                <div class="mb-3">
                    <div class="form-label-sm">Full Name *</div>
                    <input type="text" class="form-control-custom" id="full-name" placeholder="John Doe" value="{{ $defaultAddress->name ?? '' }}">
                </div>

                <div class="mb-3">
                    <div class="form-label-sm">Phone Number *</div>
                    <div class="input-icon-wrap">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="tel" class="form-control-custom" id="phone" placeholder="+92-300-1234567" value="{{ $defaultAddress->phone ?? '' }}">
                    </div>
                </div>

                <div>
                    <div class="form-label-sm">Shipping Address *</div>
                    <textarea class="form-control-custom" id="address" rows="2" placeholder="Street address, city, postal code" style="resize:none;">{{ $defaultAddress->address ?? '' }}</textarea>
                </div>

                @guest
                {{-- Guests have no saved address yet: city and country are kept with the rest (and become their saved address once they sign in) so shipping can be quoted --}}
                <div class="row g-3 mt-0">
                    <div class="col-6">
                        <div class="form-label-sm">City *</div>
                        <input type="text" class="form-control-custom" id="city" placeholder="Karachi">
                    </div>
                    <div class="col-6">
                        <div class="form-label-sm">Country *</div>
                        <input type="text" class="form-control-custom" id="country" value="Pakistan">
                    </div>
                </div>
                @endguest
            </div>

            {{-- Payment Method --}}
            <div class="checkout-card">
                <div class="section-title">
                    <i class="ri-bank-card-line"></i> Payment Method
                </div>

                <div class="payment-grid" id="payment-grid">
                    <div class="payment-option selected" data-method="credit_card" onclick="selectPayment(this)">
                        <div class="pay-radio"></div>
                        <i class="ri-bank-card-line pay-icon" style="color:#1a56db;"></i>
                        <span>Credit / Debit Card</span>
                    </div>
                    <div class="payment-option" data-method="easypaisa" onclick="selectPayment(this)">
                        <div class="pay-radio"></div>
                        <i class="ri-smartphone-line pay-icon" style="color:#3ab54a;"></i>
                        <span>Easypaisa</span>
                    </div>
                </div>

                <div class="cod-note">
                    <strong>Note:</strong> No Cash on Delivery. Online payment only. You'll complete your payment on Safepay's secure page.
                </div>
            </div>

        </div>

        {{-- ── Right: Order Summary ── --}}
        <div class="col-lg-4">
            <div class="summary-card">
                <h5 class="fw-bold mb-4">Order Summary</h5>

                <div id="checkout-items" class="mb-3"></div>

                <div class="d-flex justify-content-between mb-2 pt-2 border-top">
                    <span class="text-muted small">Subtotal</span>
                    <span class="fw-semibold small" id="co-subtotal">PKR 0</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">Shipping</span>
                    <span class="fw-semibold small" id="co-shipping">—</span>
                </div>
                <div class="text-danger small mb-3" id="co-shipping-error"></div>
                <div class="d-flex justify-content-between mb-4 fw-bold" style="font-size:1.05rem;">
                    <span>Total</span>
                    <span id="co-total" style="color:#ec4899;">PKR 200</span>
                </div>

                <button class="place-order-btn" onclick="placeOrder()">Place Order</button>
                @guest
                    <p class="text-muted small text-center mt-3 mb-0">
                        Next you'll log in or create an account, then pay securely. Your cart and details are kept safe meanwhile.
                    </p>
                @endguest
            </div>
        </div>

    </div>
</div>
</div>
@endsection

{{-- ── Order Confirmed Overlay ── --}}
<div class="confirm-overlay" id="confirm-overlay">
    <div class="confirm-card">
        <div class="confirm-icon">
            <i class="ri-check-line"></i>
        </div>
        <div class="confirm-title" id="conf-title">Order Confirmed! 🎉</div>
        <p class="text-muted mb-0" style="font-size:.9rem;" id="conf-subtitle">
            Thank you for your purchase! Your mystery adventure begins now.
        </p>
        <div id="confirm-orders-list"></div>
        <div class="d-flex gap-2 justify-content-center">
            <button class="track-btn" id="conf-track-btn"
                    onclick="window.location.href='/orders/'+this.dataset.orderUuid">
                View Order
            </button>
            <button class="continue-shop-btn" onclick="window.location.href='{{ url('/shop') }}'">Continue Shopping</button>
        </div>
    </div>
</div>

@section('script')
<script>
const CART_KEY       = 'rv_cart';
const DESELECTED_KEY = 'rv_cart_deselected';   // written by the cart page
const IS_AUTH    = {{ auth()->check() ? 'true' : 'false' }};
const CSRF_TOKEN = '{{ csrf_token() }}';
const SHIPPING_FEE_URL = '{{ route('checkout.shipping-fee') }}';

let SHIPPING = 0;
let SHIPPING_AVAILABLE = false;
let SELECTED_ADDRESS_ID = {{ optional($defaultAddress)->id ?? 'null' }};
// Set when a guest has just signed in from "Place Order": checkout carries on to payment by itself
const RESUME = @json($resume ?? null);

function fmt(n) { return 'PKR ' + Number(n).toLocaleString(); }
/* Price plus the crossed-out regular price while the item is on sale. */
function priceHtml(amount, original) {
    return fmt(amount) + (original && original > amount
        ? ' <s style="color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">' + fmt(original) + '</s>'
        : '');
}
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/* ── cart selection (chosen on the cart page) ────────────────────────────── */
function readCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch(e) { return []; }
}
function itemKey(item) {
    return item.id.startsWith('db-') ? item.id : item.id + '|' + (item.subtitle || '');
}
/* Cart items the user left selected — everything except the deselected set. */
function getSelectedCart() {
    let deselected = new Set();
    try { deselected = new Set(JSON.parse(localStorage.getItem(DESELECTED_KEY) || '[]')); } catch(e) {}
    return readCart().filter(item => !deselected.has(itemKey(item)));
}
/* After an order, drop just the ordered items; unselected ones stay in the cart. */
function removeOrderedItemsFromCart() {
    const ordered   = new Set(getSelectedCart().map(itemKey));
    const remaining = readCart().filter(item => !ordered.has(itemKey(item)));

    if (remaining.length) {
        localStorage.setItem(CART_KEY, JSON.stringify(remaining));
    } else {
        localStorage.removeItem(CART_KEY);
        localStorage.removeItem(DESELECTED_KEY);
    }
}

/* Refresh db-backed rows from the server before showing totals, so the
   summary matches what the order will be charged (a sale may have started
   or ended since the item was added). The server prices the order itself. */
async function syncPricesFromDb() {
    if (!IS_AUTH) return;
    try {
        const res = await fetch('{{ route('cart.index') }}', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const fresh = new Map(((await res.json()).items || []).map(i => [i.id, i]));
        const cart  = readCart()
            .filter(item => !item.id.startsWith('db-') || fresh.has(item.id))
            .map(item => fresh.has(item.id) ? { ...item, ...fresh.get(item.id) } : item);
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    } catch (e) {
        console.error('Cart price sync failed:', e);
    }
}

/* ── populate order summary from the selected cart items ─────────────────── */
function loadSummary() {
    const cart = getSelectedCart();

    const container = document.getElementById('checkout-items');

    if (!cart.length) {
        container.innerHTML = readCart().length
            ? '<p class="text-muted small">No items selected. <a href="{{ url('/cart') }}">Go back to your cart</a> to choose what to check out.</p>'
            : '<p class="text-muted small">No items in cart.</p>';
        document.getElementById('co-subtotal').textContent = fmt(0);
        document.getElementById('co-total').textContent    = fmt(0);
        return;
    }

    let subtotal = 0;
    container.innerHTML = cart.map(item => {
        const lineTotal = item.unitPrice * item.qty;
        const display   = item.preorder ? Math.round(lineTotal * 0.4) : lineTotal;
        const original  = item.originalPrice && !item.preorder ? item.originalPrice * item.qty : null;
        subtotal += display;
        return `
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="order-item-img-placeholder">
                ${item.image ? `<img src="${item.image}" style="width:100%;height:100%;object-fit:cover;border-radius:.5rem;" onerror="this.style.display='none'" alt="${item.name}">` : ''}
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold" style="font-size:.875rem;">${item.name}</div>
                <div class="text-muted" style="font-size:.75rem;">
                    ${item.subtitle ? item.subtitle + ' · ' : ''}Qty: ${item.qty}
                    ${item.preorder ? '<span class="ms-1" style="color:#d97706;">(40% advance)</span>' : ''}
                </div>
                ${item.comment ? `<div class="item-comment mt-1"><i class="ri-chat-1-line me-1"></i>${escapeHtml(item.comment)}</div>` : ''}
            </div>
            <div class="item-line-price">${priceHtml(display, original)}</div>
        </div>`;
    }).join('');

    document.getElementById('co-subtotal').textContent = fmt(subtotal);

    if (SHIPPING_AVAILABLE) {
        document.getElementById('co-shipping').textContent = fmt(SHIPPING);
        document.getElementById('co-total').textContent    = fmt(subtotal + SHIPPING);
    } else {
        document.getElementById('co-total').textContent = fmt(subtotal);
    }
}

/* ── selecting a saved address fills the fields and re-quotes shipping ──── */
function fillFromAddress(addressId) {
    const option = document.querySelector(`#saved-address option[value="${addressId}"]`);
    SELECTED_ADDRESS_ID = addressId ? Number(addressId) : null;

    if (option) {
        document.getElementById('full-name').value = option.dataset.name || '';
        document.getElementById('phone').value      = option.dataset.phone || '';
        document.getElementById('address').value    = option.dataset.address || '';
    }

    fetchShippingFee();
}

async function fetchShippingFee() {
    const errorEl = document.getElementById('co-shipping-error');
    const shipEl  = document.getElementById('co-shipping');
    errorEl.textContent = '';

    if (!SELECTED_ADDRESS_ID) {
        SHIPPING_AVAILABLE = false;
        SHIPPING = 0;
        shipEl.textContent = '—';
        loadSummary();
        return;
    }

    shipEl.textContent = 'Calculating…';

    try {
        const res  = await fetch(`${SHIPPING_FEE_URL}?address_id=${SELECTED_ADDRESS_ID}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (res.ok && data.available) {
            SHIPPING_AVAILABLE = true;
            SHIPPING = Number(data.shipping_fee);
        } else {
            SHIPPING_AVAILABLE = false;
            SHIPPING = 0;
            shipEl.textContent = '—';
            errorEl.textContent = data.message || 'Delivery is not available for this location.';
        }
    } catch (e) {
        SHIPPING_AVAILABLE = false;
        SHIPPING = 0;
        shipEl.textContent = '—';
        errorEl.textContent = 'Unable to calculate shipping. Please try again.';
    }

    loadSummary();
}

function selectPayment(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
}

/* ── show the success overlay ────────────────────────────────────────────── */
function showConfirmation(orders, methodLabel) {
    const multi = orders.length > 1;

    document.getElementById('conf-subtitle').textContent = multi
        ? 'Your normal items are on their way, and your preorder is confirmed with a 40% advance.'
        : 'Thank you for your purchase! Your mystery adventure begins now.';

    document.getElementById('confirm-orders-list').innerHTML = orders.map(order => {
        const isPreorder  = order.order_type === 'pre_order';
        const chargedNow  = order.charged_amount;
        const statusLabel = isPreorder ? 'Awaiting stock' : 'Processing';
        return `
        <div class="confirm-meta">
            <div>
                <div class="confirm-meta-label">${isPreorder ? 'Preorder Tracking Number' : 'Tracking Number'}</div>
                <div class="confirm-meta-value order-id">${order.tracking_number}</div>
            </div>
            <div>
                <div class="confirm-meta-label">${isPreorder ? 'Advance Paid (40%)' : 'Total Paid'}</div>
                <div class="confirm-meta-value">${fmt(chargedNow)}</div>
            </div>
            <div>
                <div class="confirm-meta-label">Payment Method</div>
                <div class="confirm-meta-value">${methodLabel}</div>
            </div>
            <div>
                <div class="confirm-meta-label">Status</div>
                <div class="confirm-meta-value status">${statusLabel}</div>
            </div>
            ${isPreorder ? `<div style="grid-column:1/-1;font-size:.78rem;color:#6b7280;">Remaining ${fmt(order.remaining_amount)} is due once this item is back in stock — we'll notify you.</div>` : ''}
        </div>`;
    }).join('');

    document.getElementById('conf-track-btn').dataset.orderUuid = orders[0].uuid;
    document.getElementById('confirm-overlay').classList.add('show');
}

/* ── hand off to Safepay for one order, chaining to the next if given ───── */
async function startPayment(orderUuid, nextOrderUuid) {
    try {
        const res = await fetch(`/orders/${orderUuid}/pay`, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ next_order_uuid: nextOrderUuid }),
        });
        const data = await res.json();

        if (!res.ok || !data.redirect) {
            showToast(data.message || 'Unable to start payment. Please try again.', 'error');
            const btn = document.querySelector('.place-order-btn');
            btn.disabled = false;
            btn.textContent = 'Place Order';
            return;
        }

        window.location.href = data.redirect;
    } catch (e) {
        console.error('Payment start failed:', e);
        showToast('Something went wrong starting payment. Please try again.', 'error');
        const btn = document.querySelector('.place-order-btn');
        btn.disabled = false;
        btn.textContent = 'Place Order';
    }
}

/* ── guests: phone in the "+countrycode…" form the server needs ──────────── */
function normalizePhone(raw, country) {
    let p = (raw || '').replace(/[\s\-().]/g, '');
    if (p.startsWith('00')) p = '+' + p.slice(2);
    // a local Pakistani number (0300 1234567) → +92 300 1234567
    if (/^0\d{10}$/.test(p) && /^pakistan$/i.test((country || '').trim())) p = '+92' + p.slice(1);
    return /^\+[1-9]\d{7,14}$/.test(p) ? p : null;
}

/* ── items a guest added live only in this browser; once signed in they move into the
      account's cart, which is what an order is made from ────────────────────────────── */
async function mergeGuestCart() {
    if (!IS_AUTH) return;

    const cart       = readCart();
    const guestItems = cart.filter(i => !i.id.startsWith('db-'));
    if (!guestItems.length) return;

    let deselected;
    try { deselected = new Set(JSON.parse(localStorage.getItem(DESELECTED_KEY) || '[]')); } catch (e) { deselected = new Set(); }

    const merged = cart.filter(i => i.id.startsWith('db-'));
    let notCarried = 0;

    for (let i = 0; i < guestItems.length; i++) {
        const item = guestItems[i];

        if (item.payload) {
            try {
                const res = await fetch('{{ route('cart.store') }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    // the quantity and note as they are now, not as first added
                    body: JSON.stringify({ ...item.payload, qty: item.qty, comment: item.comment ?? item.payload.comment ?? null }),
                });

                if (res.ok) {
                    const { cartItem } = await res.json();
                    const same = merged.find(m => m.id === cartItem.id);
                    if (same) Object.assign(same, cartItem); else merged.push(cartItem);
                    if (deselected.has(itemKey(item))) deselected.add(itemKey(cartItem));
                } else {
                    notCarried++;
                }
            } catch (e) {
                notCarried++;
            }
        } else {
            notCarried++;   // added before carts could move accounts, or not a catalog product
        }

        // saved after every item, so an interrupted merge never adds the same item twice
        localStorage.setItem(CART_KEY, JSON.stringify([...merged, ...guestItems.slice(i + 1)]));
    }

    try { localStorage.setItem(DESELECTED_KEY, JSON.stringify([...deselected])); } catch (e) {}
    if (window.updateCartBadge) updateCartBadge();

    if (notCarried) {
        showToast('Some items in your cart could not be carried over to your account. Please add them again.', 'error');
    }
}

/* ── just signed in from "Place Order": pick up where the guest left off ─── */
function continueAfterSignIn() {
    const option = document.querySelector(`.payment-option[data-method="${RESUME.payment_method}"]`);
    if (option) selectPayment(option);

    const text = document.getElementById('resume-banner-text');

    if (getSelectedCart().length && SELECTED_ADDRESS_ID && SHIPPING_AVAILABLE) {
        if (text) text.textContent = "You're signed in. Taking you to payment…";
        placeOrder();
    } else if (text) {
        text.textContent = "You're signed in. Check the details below, then place your order.";
    }
}

/* ── place order ─────────────────────────────────────────────────────────── */
async function placeOrder() {
    const name    = document.getElementById('full-name').value.trim();
    const phone   = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const method  = document.querySelector('.payment-option.selected')?.dataset.method;

    if (!name || !phone || !address) {
        showToast('Please fill in all required shipping fields.', 'error');
        return;
    }
    if (!method) {
        showToast('Please select a payment method.', 'error');
        return;
    }
    if (IS_AUTH && (!SELECTED_ADDRESS_ID || !SHIPPING_AVAILABLE)) {
        showToast('Delivery is not available for this location.', 'error');
        return;
    }

    // Guests: city, and a phone with its country code, are needed to carry the order through sign-in
    let guestDetails = null;
    if (!IS_AUTH) {
        const city      = document.getElementById('city')?.value.trim();
        const country   = document.getElementById('country')?.value.trim();
        const phoneIntl = normalizePhone(phone, country);

        if (!city || !country) {
            showToast('Please enter your city and country.', 'error');
            return;
        }
        if (!phoneIntl) {
            showToast('Phone number must start with + and country code (e.g. +923001234567).', 'error');
            return;
        }
        guestDetails = { name, phone: phoneIntl, address, city, country, payment_method: method };
    }

    const selectedItems = getSelectedCart();
    if (!selectedItems.length) {
        showToast('Select at least one item in your cart to check out.', 'error');
        return;
    }

    const btn = document.querySelector('.place-order-btn');
    btn.disabled = true;
    btn.textContent = 'Placing Order…';

    /* ── authenticated: send to server ──────────────────────────────────── */
    if (IS_AUTH) {
        try {
            const res = await fetch('{{ route('orders.store') }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    shipping_name:    name,
                    shipping_phone:   phone,
                    shipping_address: address,
                    payment_method:   method,
                    address_id:       SELECTED_ADDRESS_ID,
                    // only db-backed rows exist server-side: 'db-12' → 12
                    cart_item_ids:    selectedItems
                        .filter(i => i.id.startsWith('db-'))
                        .map(i => Number(i.id.slice(3))),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Order failed.');
                showToast(msg, 'error');
                btn.disabled = false;
                btn.textContent = 'Place Order';
                return;
            }

            // Drop the ordered items from the localStorage cart (the server
            // already removed them from the DB cart; unselected ones remain)
            removeOrderedItemsFromCart();

            // Orders are created unpaid — hand off to Safepay for the first
            // one now. A mixed cart (normal + preorder) makes two orders;
            // the second is chained in via next_order_uuid and picked up
            // automatically once the first payment succeeds.
            const orders = data.orders;
            btn.textContent = 'Redirecting to payment…';
            await startPayment(orders[0].uuid, orders[1]?.uuid ?? null);

        } catch (e) {
            console.error('Order error:', e);
            showToast('Something went wrong. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Place Order';
        }
        return;
    }

    /* ── guest: log in or register first ─────────────────────────────────────
       The details wait in the guest's own session for a short while (CheckoutResume); the cart
       stays in this browser. Signing in brings them back here to carry on to payment. ────── */
    try {
        const res  = await fetch('{{ route('checkout.resume') }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body:    JSON.stringify(guestDetails),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Something went wrong. Please try again.'), 'error');
            btn.disabled = false;
            btn.textContent = 'Place Order';
            return;
        }

        btn.textContent = 'Taking you to sign in…';
        window.location.href = data.redirect;
    } catch (e) {
        console.error('Checkout hand-off failed:', e);
        showToast('Something went wrong. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = 'Place Order';
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await mergeGuestCart();
    await syncPricesFromDb();
    loadSummary();
    if (IS_AUTH && SELECTED_ADDRESS_ID) {
        await fetchShippingFee();
    }
    if (RESUME) continueAfterSignIn();
});

// The admin changed prices / discounts while checkout is open (layouts/live-updates)
document.addEventListener('live:catalog', async () => {
    await syncPricesFromDb();
    loadSummary();
});
</script>
@endsection
