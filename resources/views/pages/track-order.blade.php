@extends('layouts.landing-page-layout')

@section('css')
<style>
    .track-wrapper {
        background: linear-gradient(to bottom,#eef2ff,#f5f3ff);
        min-height: 100vh;
    }

    /* ── Header ── */
    .track-icon-wrap {
        width: 3.5rem; height: 3.5rem; border-radius: 50%;
        background: #e0e7ff;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.25rem;
    }
    .track-icon-wrap i { font-size: 1.5rem; color: #6366f1; }

    /* ── Search card ── */
    .search-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 1.75rem 2rem; max-width: 38rem; margin: 0 auto;
    }
    .track-input {
        border: 1.5px solid #e5e7eb; border-radius: .6rem;
        padding: .65rem 1rem; font-size: .875rem;
        outline: none; width: 100%;
        transition: border-color .2s;
        background: #f9fafb; color: #1f2937;
    }
    .track-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
    .track-input::placeholder { color: #9ca3af; }
    .track-search-btn {
        background: linear-gradient(135deg,#6366f1,#8b5cf6);
        border: 0; border-radius: .6rem; color: #fff;
        font-weight: 600; font-size: .875rem;
        padding: .65rem 1.25rem; white-space: nowrap;
        display: flex; align-items: center; gap: .4rem;
        transition: opacity .2s;
    }
    .track-search-btn:hover { opacity: .88; color: #fff; }
    .track-error { font-size: .8rem; color: #dc2626; margin-top: .5rem; margin-bottom: 0; }

    /* ── Recent orders ── */
    .recent-order-row {
        background: #fff; border-radius: .75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 1rem 1.25rem; margin-bottom: .75rem;
        display: flex; align-items: center; gap: 1rem;
        transition: box-shadow .2s;
    }
    .recent-order-row:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); }
    .order-icon-sm {
        width: 2.5rem; height: 2.5rem; border-radius: 50%;
        background: #ede9fe;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .order-icon-sm i { font-size: 1.1rem; color: #7c3aed; }
    .order-id-text  { font-size: .875rem; font-weight: 700; color: #1f2937; }
    .order-date-text{ font-size: .75rem; color: #9ca3af; }
    .order-price    {
        font-weight: 700; font-size: .9rem;
        background: linear-gradient(to right,#ec4899,#a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .status-pill {
        font-size: .7rem; font-weight: 700; border-radius: .4rem;
        padding: .25rem .65rem; color: #fff; text-transform: capitalize;
    }
    .pill-confirmed      { background: #059669; }
    .pill-processing     { background: #2563eb; }
    .pill-shipped         { background: #7c3aed; }
    .pill-delivered       { background: #16a34a; }
    .pill-awaiting_stock  { background: #b45309; }
    .pill-payment_due     { background: #dc2626; }
    .pill-cancelled       { background: #6b7280; }

    .row-track-btn {
        border: 1.5px solid #e5e7eb; border-radius: .5rem;
        background: #fff; color: #374151;
        font-size: .82rem; font-weight: 500; padding: .4rem 1.1rem;
        transition: border-color .2s; white-space: nowrap;
        flex-shrink: 0;
    }
    .row-track-btn:hover { border-color: #6366f1; color: #6366f1; }

    /* ── Track result card ── */
    .result-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 1.75rem; max-width: 38rem; margin: 1.5rem auto 0;
    }
    .result-view-link { font-size: .8rem; font-weight: 600; color: #6366f1; }
    .timeline { position: relative; padding-left: 1.5rem; }
    .timeline::before {
        content: ''; position: absolute; left: .55rem; top: .5rem;
        bottom: .5rem; width: 2px; background: #e5e7eb;
    }
    .tl-step { position: relative; margin-bottom: 1.25rem; }
    .tl-step:last-child { margin-bottom: 0; }
    .tl-dot {
        position: absolute; left: -1.5rem; top: .2rem;
        width: .85rem; height: .85rem; border-radius: 50%;
        border: 2px solid #e5e7eb; background: #fff;
    }
    .tl-dot.done  { background: #7c3aed; border-color: #7c3aed; }
    .tl-dot.active{ background: #ec4899; border-color: #ec4899; box-shadow: 0 0 0 3px rgba(236,72,153,.2); }
    .tl-dot.cancelled { background: #6b7280; border-color: #6b7280; }
    .tl-label { font-size: .875rem; font-weight: 600; color: #1f2937; }
    .tl-date  { font-size: .75rem; color: #9ca3af; }
</style>
@endsection

@section('content')
<div class="track-wrapper py-5">
<div class="container px-4">

    {{-- Header --}}
    <div class="text-center mb-5">
        <div class="track-icon-wrap">
            <i class="ri-box-3-line"></i>
        </div>
        <h1 class="fw-bold mb-2" style="font-size:clamp(1.75rem,4vw,2.5rem);color:#4f46e5;">
            Track Your Order
        </h1>
        <p class="text-muted mx-auto" style="max-width:32rem;font-size:.95rem;">
            Enter your order ID or tracking number to see real-time updates on your delivery
        </p>
    </div>

    {{-- Search card --}}
    <div class="search-card mb-5">
        <div class="fw-semibold mb-2" style="font-size:.9rem;">Order ID / Tracking Number</div>
        <div class="d-flex gap-2 mb-2">
            <input type="text" class="track-input" id="track-input"
                   placeholder="Enter order ID or tracking number..."
                   value="{{ $query }}">
            <button class="track-search-btn" onclick="trackOrder()">
                <i class="ri-search-line"></i> Track
            </button>
        </div>
        <p class="text-muted mb-0" style="font-size:.78rem;">
            You can find your order ID in your confirmation email or account orders page
        </p>
        <p class="track-error" id="track-error" style="display:none;"></p>
    </div>

    {{-- Track result (shown after search) --}}
    <div id="track-result" style="display:none;">
        <div class="result-card">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="fw-bold" id="res-order-id" style="font-size:.95rem;color:#4f46e5;"></div>
                    <div class="text-muted" id="res-date" style="font-size:.78rem;"></div>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <span class="status-pill" id="res-status-pill"></span>
                    <a href="#" id="res-view-link" class="result-view-link">View Order Details</a>
                </div>
            </div>
            <div class="timeline" id="res-timeline"></div>
        </div>
    </div>

    {{-- Recent orders (data-live-region: re-rendered when an order changes, layouts/live-updates) --}}
    <div id="recent-orders-section" class="mt-5 pt-3" data-live-region="track-recent-orders">
        <h5 class="fw-bold text-center mb-4">Your Recent Orders</h5>
        <div id="recent-orders-list" style="max-width:38rem;margin:0 auto;">
            @foreach($orders as $order)
            <div class="recent-order-row">
                <div class="order-icon-sm"><i class="ri-box-3-line"></i></div>
                <div class="flex-grow-1">
                    <div class="order-id-text">Order #{{ $order->id }}</div>
                    <div class="order-date-text">{{ $order->order_date?->format('d M Y') }} &middot; {{ $order->tracking_number }}</div>
                </div>
                <div class="ms-auto d-flex flex-column align-items-end gap-1">
                    <div class="order-price">PKR {{ number_format($order->total_amount) }}</div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span class="status-pill pill-{{ $order->order_status }}" style="line-height:1.6;">{{ str_replace('_', ' ', $order->order_status) }}</span>
                        <button class="row-track-btn" style="height:1.9rem;line-height:1;" onclick="fillAndTrack({{ Js::from($order->tracking_number ?: $order->uuid) }})">Track</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="no-orders" class="text-center text-muted py-4" style="display:{{ $orders->isEmpty() ? '' : 'none' }};font-size:.9rem;">
            No recent orders found.
        </div>
    </div>

</div>
</div>
@endsection

@section('script')
<script>
const LOOKUP_URL = '{{ route('track-order.lookup') }}';
const initialResult = @json($selected);

function fmtDateLabel(step) {
    return step.date ?? '';
}

let trackedQuery = null;   // the order currently shown, refreshed live below

function renderResult(order, scroll = true) {
    document.getElementById('res-order-id').textContent = 'Order #' + order.id + ' · ' + order.tracking_number;
    document.getElementById('res-date').textContent = 'Placed on ' + order.order_date;

    const pillEl = document.getElementById('res-status-pill');
    pillEl.className = 'status-pill pill-' + order.status;
    pillEl.textContent = order.status_label;

    document.getElementById('res-view-link').href = order.action_url;

    const timelineEl = document.getElementById('res-timeline');
    timelineEl.innerHTML = order.timeline.map(step => `
        <div class="tl-step">
            <div class="tl-dot ${step.state}"></div>
            <div class="tl-label">${step.label}</div>
            <div class="tl-date">${fmtDateLabel(step)}</div>
        </div>
    `).join('');

    document.getElementById('track-error').style.display = 'none';
    const resultEl = document.getElementById('track-result');
    resultEl.style.display = 'block';
    if (scroll) resultEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showError(message) {
    document.getElementById('track-result').style.display = 'none';
    const errorEl = document.getElementById('track-error');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
}

function fillAndTrack(id) {
    document.getElementById('track-input').value = id;
    trackOrder();
}

async function trackOrder() {
    const val = document.getElementById('track-input').value.trim();

    if (!val) {
        showError('Please enter an order ID or tracking number.');
        return;
    }

    try {
        const res = await fetch(`${LOOKUP_URL}?query=${encodeURIComponent(val)}`, {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            showError(data.message || 'Order not found. Please check your order ID or tracking number.');
            return;
        }

        trackedQuery = val;
        renderResult(data);
    } catch (e) {
        showError('Something went wrong. Please try again.');
    }
}

// The admin changed one of this shopper's orders (layouts/live-updates): quietly
// refresh the status and timeline of the order on screen, without scrolling.
document.addEventListener('live:orders', async () => {
    if (!trackedQuery) return;
    try {
        const res = await fetch(`${LOOKUP_URL}?query=${encodeURIComponent(trackedQuery)}`, { headers: { 'Accept': 'application/json' } });
        if (res.ok) renderResult(await res.json(), false);
    } catch (e) {}
});

document.addEventListener('DOMContentLoaded', () => {
    if (initialResult) {
        trackedQuery = @json($query);
        renderResult(initialResult);
    }
});
</script>
@endsection
