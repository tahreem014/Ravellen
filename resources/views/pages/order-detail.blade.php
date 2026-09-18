@extends('layouts.landing-page-layout')

@section('css')
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
        border-radius: 2rem; text-transform: capitalize;
    }
    .status-pending, .status-awaiting_stock  { background: #fef3c7; color: #92400e; }
    .status-payment_due                       { background: #fee2e2; color: #991b1b; }
    .status-processing                        { background: #dbeafe; color: #1e40af; }
    .status-shipped                           { background: #e0e7ff; color: #3730a3; }
    .status-delivered                         { background: #dcfce7; color: #166534; }
    .status-cancelled                         { background: #f3f4f6; color: #4b5563; }

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
@endsection

@section('content')
<div class="order-wrapper py-5">
<div class="container px-4" style="max-width: 900px;">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="fw-bold mb-1" style="font-size:1.75rem;background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Order #{{ $order->id }}
            </h1>
            <p class="text-muted small mb-0">{{ $order->tracking_number }} &middot; placed {{ $order->order_date?->format('d M Y') ?? $order->created_at->format('d M Y') }}</p>
        </div>
        <span class="status-pill status-{{ $order->order_status }}">{{ str_replace('_', ' ', $order->order_status) }}</span>
    </div>

    @if($order->order_type === 'pre_order' && $order->order_status === 'payment_due')
    <div class="preorder-note d-flex align-items-start gap-2 mb-4">
        <i class="ri-information-line mt-1"></i>
        <div>Your preorder item is now available. Please complete the remaining payment below to move your order into processing.</div>
    </div>
    @elseif($order->order_type === 'pre_order' && $order->order_status === 'awaiting_stock')
    <div class="preorder-note d-flex align-items-start gap-2 mb-4">
        <i class="ri-time-line mt-1"></i>
        <div>This is a preorder. You've paid the 40% advance — we'll email and notify you here as soon as the item is back in stock so you can pay the remaining balance.</div>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="order-card mb-4">
                <div class="section-title"><i class="ri-shopping-bag-3-line"></i> Items</div>
                @foreach($order->items as $item)
                <div class="order-item-row">
                    <div>
                        <div class="order-item-name">{{ $item->product?->name ?? 'Product' }}</div>
                        <div class="order-item-meta">
                            {{ $item->productVariant?->variant_name ? 'Variant: '.$item->productVariant->variant_name.' · ' : '' }}Qty: {{ $item->qty }}
                            @if($item->comment)
                                <br><span style="font-style:italic;">"{{ $item->comment }}"</span>
                            @endif
                        </div>
                    </div>
                    <div class="order-item-price">PKR {{ number_format($item->price * $item->qty) }}</div>
                </div>
                @endforeach
            </div>

            <div class="order-card">
                <div class="section-title"><i class="ri-map-pin-line"></i> Shipping</div>
                <p class="mb-1 fw-semibold small">{{ $order->shipping_name }} &middot; {{ $order->shipping_phone }}</p>
                <p class="text-muted small mb-0">{{ $order->shipping_address }}</p>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="order-card">
                <div class="section-title"><i class="ri-bank-card-line"></i> Payment</div>

                <div class="money-row"><span class="text-muted">Payment method</span><span class="text-capitalize">{{ $order->payment_method }}</span></div>
                <div class="money-row"><span class="text-muted">Shipping fee</span><span>PKR {{ number_format($order->shipping_fee) }}</span></div>

                @if($order->order_type === 'pre_order')
                    <div class="money-row"><span class="text-muted">Advance paid (40%)</span><span>PKR {{ number_format($order->advance_amount) }}</span></div>
                    <div class="money-row">
                        <span class="text-muted">Remaining balance</span>
                        <span>PKR {{ number_format($order->remaining_amount) }}</span>
                    </div>
                    <div class="money-row total"><span>Order total</span><span>PKR {{ number_format($order->total_amount) }}</span></div>

                    @if($order->order_status === 'payment_due')
                        <button class="pay-remaining-btn" id="pay-remaining-btn" onclick="payRemaining()">
                            Pay Remaining — PKR {{ number_format($order->remaining_amount) }}
                        </button>
                    @elseif($order->payment_stage === 'completed')
                        <p class="text-muted small mt-3 mb-0"><i class="ri-checkbox-circle-line" style="color:#22c55e;"></i> Remaining balance paid {{ $order->remaining_paid_at?->format('d M Y') }}.</p>
                    @endif
                @else
                    <div class="money-row total"><span>Total paid</span><span>PKR {{ number_format($order->total_amount) }}</span></div>
                @endif
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@section('script')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const ORDER_ID   = {{ $order->id }};

async function payRemaining() {
    const btn = document.getElementById('pay-remaining-btn');
    btn.disabled = true;
    btn.textContent = 'Processing…';

    try {
        const res  = await fetch(`/orders/${ORDER_ID}/pay-remaining`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Payment failed. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Pay Remaining';
            return;
        }

        window.location.reload();
    } catch (e) {
        console.error('Pay remaining failed:', e);
        alert('Something went wrong. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Pay Remaining';
    }
}
</script>
@endsection
