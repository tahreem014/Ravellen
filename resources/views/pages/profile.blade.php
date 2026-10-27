@extends('layouts.landing-page-layout')
@section('title', 'My Profile')

@section('css')
<style>
    .profile-wrapper { background: linear-gradient(to bottom,#fdf2f8,#f5f3ff); min-height: 100vh; }

    /* ── Left card ── */
    .profile-sidebar {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 2rem 1.5rem; text-align: center;
    }
    .avatar-wrap {
        position: relative; width: 6rem; margin: 0 auto 1rem; cursor: pointer;
    }
    .avatar-ring {
        width: 6rem; height: 6rem; border-radius: 50%;
        border: 3px solid #fce7f3;
        overflow: hidden;
        background: #fdf2f8;
    }
    .avatar-ring img { width: 100%; height: 100%; object-fit: cover; }
    .avatar-edit-badge {
        position: absolute; bottom: 0rem; right: -.15rem;
        width: 1.75rem; height: 1.75rem; border-radius: 50%;
        background: #f72585; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }
    .loyal-badge {
        display: inline-block; background: #fce7f3; color: #ec4899;
        font-size: .72rem; font-weight: 700; border-radius: 2rem;
        padding: .2rem .75rem; margin-bottom: 1rem;
    }
    .profile-meta { font-size: .82rem; color: #6b7280; text-align: left; }
    .profile-meta i { color: #ec4899; width: 1.1rem; }
    .signout-btn {
        width: 100%; border: 1.5px solid #fce7f3; border-radius: .6rem;
        background: #fff; color: #ec4899; font-weight: 600; font-size: .875rem;
        padding: .6rem 1rem; transition: background .2s;
        display: flex; align-items: center; justify-content: center; gap: .4rem;
    }
    .signout-btn:hover { background: #fdf2f8; }

    /* ── Tabs ── */
    .profile-tabs { display: flex; gap: 0; border-bottom: 0; margin-bottom: 1.5rem; }
    .profile-tab {
        flex: 1; text-align: center; padding: .55rem .5rem;
        font-size: .875rem; font-weight: 500; color: #6b7280;
        cursor: pointer; border-radius: 2rem; border: 0; background: none;
        transition: all .2s;
    }
    .profile-tab.active {
        background: #f72585;
        color: #fff; font-weight: 600;
    }
    .profile-tab:hover:not(.active) { color: #ec4899; }

    /* ── Stat cards ── */
    .stat-card {
        background: #fff; border-radius: .75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        padding: 1.25rem 1.5rem;
    }
    .stat-label { font-size: .8rem; color: #6b7280; margin-bottom: .4rem; }
    .stat-value { font-size: 1.6rem; font-weight: 800; color: #1f2937; }
    .stat-value.pink { color: #ec4899; }

    /* ── Content card ── */
    .content-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 2px 16px rgba(0,0,0,.07);
        padding: 1.75rem;
    }
    .section-heading { font-size: 1rem; font-weight: 700; color: #1f2937; margin-bottom: .2rem; }
    .section-sub { font-size: .8rem; color: #9ca3af; margin-bottom: 1.25rem; }

    /* ── Order rows ── */
    .order-row {
        border: 1px solid #f3f4f6; border-radius: .75rem;
        padding: 1rem 1.25rem; margin-bottom: 1rem;
    }
    .order-meta-label { font-size: .7rem; color: #9ca3af; margin-bottom: .15rem; }
    .order-meta-val   { font-size: .82rem; font-weight: 600; color: #1f2937; }
    .order-meta-val.pink { color: #ec4899; }

    .status-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 700; border-radius: 2rem;
        padding: .25rem .75rem;
    }
    .status-delivered { background: #dcfce7; color: #16a34a; }
    .status-confirmed  { background: #ecfdf5; color: #059669; }
    .status-processing { background: #eff6ff; color: #2563eb; }
    .status-pending    { background: #fefce8; color: #ca8a04; }
    .status-shipped        { background: #ede9fe; color: #7c3aed; }
    .status-out_for_delivery { background: #fce7f3; color: #be185d; }
    .status-awaiting_stock { background: #fef3c7; color: #92400e; }
    .status-payment_due    { background: #fee2e2; color: #991b1b; }
    .status-cancelled      { background: #f3f4f6; color: #4b5563; }
    .status-approved       { background: #dcfce7; color: #16a34a; }
    .status-rejected       { background: #fee2e2; color: #991b1b; }

    .refund-card { background:#faf5ff; border:1px solid #ede9fe; border-radius:.6rem; padding:.85rem 1rem; margin:.25rem 0 1rem 3.75rem; }
    .refund-card-row { display:flex; justify-content:space-between; gap:1rem; font-size:.78rem; padding:.2rem 0; }
    .refund-card-label { color:#9ca3af; }
    .refund-card-val { color:#1f2937; font-weight:600; text-align:right; }
    .refund-actions { display:flex; gap:.5rem; margin-top:.5rem; }
    .refund-request-btn {
        border:1.5px solid #a855f7; border-radius:.5rem; background:#fff; color:#7c3aed;
        font-size:.78rem; font-weight:600; padding:.35rem .9rem; margin-left:3.75rem;
    }
    .refund-request-btn:hover { background:#faf5ff; }
    .refund-cancel-btn {
        border:1.5px solid #fecaca; border-radius:.5rem; background:#fff; color:#ef4444;
        font-size:.78rem; font-weight:600; padding:.3rem .8rem;
    }
    .refund-cancel-btn:hover { background:#fef2f2; }
    .pay-remaining-item-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .5rem; color: #fff;
        font-size: .78rem; font-weight: 700; padding: .35rem .9rem;
        transition: opacity .2s; white-space: nowrap;
    }
    .pay-remaining-item-btn:hover { opacity: .88; color: #fff; }
    .pay-remaining-item-btn:disabled { opacity: .6; cursor: not-allowed; }

    .order-item-img {
        width: 3rem; height: 3rem; border-radius: .4rem;
        object-fit: cover; background: #f3e8ff;
    }
    .order-item-name  { font-size: .875rem; font-weight: 600; color: #1f2937; }
    .order-item-sub   { font-size: .75rem; color: #6b7280; }
    .order-item-price { font-size: .82rem; font-weight: 700; color: #ec4899; }

    .track-btn {
        background: #f72585;
        border: 0; border-radius: .5rem; color: #fff;
        font-size: .8rem; font-weight: 600; padding: .45rem 1rem;
        transition: opacity .2s; white-space: nowrap;
    }
    .track-btn:hover { opacity: .88; color: #fff; }
    .invoice-btn {
        border: 1.5px solid #e5e7eb; border-radius: .5rem;
        background: #fff; color: #374151;
        font-size: .8rem; font-weight: 500; padding: .4rem 1rem;
        transition: border-color .2s; white-space: nowrap;
    }
    .invoice-btn:hover { border-color: #a855f7; color: #7c3aed; }

    /* ── Address cards ── */
    .address-card {
        border: 1.5px solid #e5e7eb; border-radius: .75rem;
        padding: 1.25rem; position: relative;
    }
    .address-card.default { border-color: #ec4899; }
    .default-badge {
        position: absolute; top: .75rem; right: .75rem;
        background: #ec4899; color: #fff;
        font-size: .68rem; font-weight: 700;
        padding: .15rem .55rem; border-radius: 2rem;
    }
    .add-address-card {
        border: 1.5px dashed #d1d5db; border-radius: .75rem;
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; padding: 2rem 1rem;
        cursor: pointer; color: #9ca3af; transition: border-color .2s;
        min-height: 8rem;
    }
    .add-address-card:hover { border-color: #a855f7; color: #7c3aed; }

    /* ── Settings ── */
    .settings-section-title {
        font-size: .9rem; font-weight: 700; color: #1f2937;
        display: flex; align-items: center; gap: .4rem; margin-bottom: 1rem;
    }
    .settings-section-title i { color: #ec4899; }
    .form-input {
        border: 1.5px solid #e5e7eb; border-radius: .6rem;
        padding: .6rem .9rem; font-size: .875rem; width: 100%;
        outline: none; transition: border-color .2s;
    }
    .form-input:focus { border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168,85,247,.1); }
    .update-btn {
        background: #f72585;
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .875rem; padding: .55rem 1.4rem;
        transition: opacity .2s;
    }
    .update-btn:hover { opacity: .88; }
    /* tab panels */
    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── Danger zone ── */
    .danger-zone {
        border: 1.5px solid #fecaca; border-radius: .75rem;
        padding: 1.25rem; background: #fef2f2;
    }
    .danger-btn {
        background: #ef4444; border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .875rem; padding: .55rem 1.4rem;
        transition: opacity .2s;
    }
    .danger-btn:hover { opacity: .88; color: #fff; }

    /* ── Address modal ── */
    .address-modal-overlay {
        position: fixed; inset: 0; background: rgba(17,24,39,.5);
        display: none; align-items: center; justify-content: center;
        z-index: 1060; padding: 1rem;
    }
    .address-modal-overlay.open { display: flex; }
    .address-modal-box {
        background: #fff; border-radius: 1rem; padding: 1.75rem;
        width: 100%; max-width: 28rem; box-shadow: 0 12px 40px rgba(0,0,0,.2);
    }
    .address-modal-close {
        background: transparent; border: 0; font-size: 1.25rem; color: #9ca3af; cursor: pointer;
    }
</style>
@endsection

@section('content')
@php
    $statusMap = [
        'delivered'      => ['label'=>'Delivered',      'class'=>'status-delivered',      'icon'=>'ri-checkbox-circle-line'],
        'confirmed'      => ['label'=>'Confirmed',      'class'=>'status-confirmed',      'icon'=>'ri-checkbox-circle-line'],
        'processing'     => ['label'=>'Processing',     'class'=>'status-processing',     'icon'=>'ri-time-line'],
        'pending'        => ['label'=>'Pending',        'class'=>'status-pending',        'icon'=>'ri-hourglass-line'],
        'shipped'        => ['label'=>'Shipped',        'class'=>'status-shipped',        'icon'=>'ri-truck-line'],
        'out_for_delivery' => ['label'=>'Out for Delivery', 'class'=>'status-out_for_delivery', 'icon'=>'ri-e-bike-2-line'],
        'awaiting_stock' => ['label'=>'Awaiting Stock', 'class'=>'status-awaiting_stock', 'icon'=>'ri-hourglass-line'],
        'payment_due'    => ['label'=>'Payment Due',    'class'=>'status-payment_due',    'icon'=>'ri-error-warning-line'],
        'cancelled'      => ['label'=>'Cancelled',      'class'=>'status-cancelled',      'icon'=>'ri-close-circle-line'],
    ];
    $refundStatusMap = [
        'pending'   => ['label'=>'Pending',   'class'=>'status-pending'],
        'approved'  => ['label'=>'Approved',  'class'=>'status-approved'],
        'rejected'  => ['label'=>'Rejected',  'class'=>'status-rejected'],
        'cancelled' => ['label'=>'Cancelled', 'class'=>'status-cancelled'],
    ];
@endphp

<div class="profile-wrapper py-5">
<div class="container px-4">
<div class="row g-4 align-items-start">

    {{-- ── Sidebar ── --}}
    <div class="col-lg-3 col-md-4">
        <div class="profile-sidebar">

            <form action="{{ route('profile.edit') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                @csrf
                @method('PUT')
                <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none"
                       onchange="document.getElementById('avatarForm').submit()">
            </form>

            <div class="avatar-wrap" onclick="document.getElementById('avatarInput').click()" title="Change avatar">
                <div class="avatar-ring">
                    <img src="{{ $profile->avatarUrl }}" alt="{{ $profile->name }}">
                </div>
                <span class="avatar-edit-badge"><i class="ri-camera-line"></i></span>
            </div>
            @error('avatar', 'updateProfile')
                <div class="text-danger mb-2" style="font-size:.75rem;">{{ $message }}</div>
            @enderror

            <h5 class="fw-bold mb-1">{{ $profile->name }}</h5>
            <p class="text-muted mb-2" style="font-size:.82rem;">{{ $profile->email }}</p>
            <span class="loyal-badge">Member since {{ $profile->memberSince }}</span>

            <div class="profile-meta mt-3 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-phone-line"></i>
                    <span>{{ $profile->phoneNumber ?: 'No phone number added yet' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="signout-btn">
                    <i class="ri-logout-box-r-line"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    {{-- ── Main content ── --}}
    <div class="col-lg-9 col-md-8">

        {{-- Tabs --}}
        <div class="profile-tabs mb-4">
            <button class="profile-tab active" onclick="switchTab('overview',this)">Overview</button>
            <button class="profile-tab" onclick="switchTab('orders',this)">Order History</button>
            <button class="profile-tab" onclick="switchTab('addresses',this)">Addresses</button>
            <button class="profile-tab" onclick="switchTab('settings',this)">Settings</button>
        </div>

        {{-- ── Overview ── (data-live-region: re-rendered when an order / refund changes, layouts/live-updates) --}}
        <div id="tab-overview" class="tab-panel active" data-live-region="profile-overview">
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value">{{ count($orders) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-label">Total Spent</div>
                        <div class="stat-value pink">PKR {{ number_format($totalSpent) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card">
                        <div class="stat-label">Wishlist Items</div>
                        <div class="stat-value">{{ $wishlistCount }}</div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="section-heading">Recent Orders</div>
                <div class="section-sub">Your latest purchases from Ravellen</div>

                @forelse($orders as $order)
                @php $st = $statusMap[$order->order_status] ?? $statusMap['pending']; @endphp
                <div class="order-row">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="font-size:.9rem;">{{ $order->tracking_number }}</div>
                            <div class="text-muted" style="font-size:.78rem;">Placed on {{ $order->order_date?->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $order->items->count() }} item{{ $order->items->count()>1?'s':'' }} · PKR {{ number_format($order->total_amount) }}</div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <span class="status-badge {{ $st['class'] }}">
                                <i class="{{ $st['icon'] }}"></i> {{ $st['label'] }}
                            </span>
                            <a href="{{ route('orders.show', $order) }}" class="invoice-btn text-decoration-none" style="font-size:.75rem;padding:.3rem .75rem;">View Details</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted" style="font-size:.85rem;">No orders yet.</p>
                @endforelse
            </div>
        </div>

        {{-- ── Order History ── (data-live-region, as above) --}}
        <div id="tab-orders" class="tab-panel" data-live-region="profile-orders">
            <div class="content-card">
                <div class="section-heading">Order History</div>
                <div class="section-sub">View and track all your orders</div>

                @forelse($orders as $order)
                @php $st = $statusMap[$order->order_status] ?? $statusMap['pending']; @endphp
                <div class="order-row mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div class="d-flex flex-wrap gap-4">
                            <div>
                                <div class="order-meta-label">Order Placed</div>
                                <div class="order-meta-val">{{ $order->order_date?->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="order-meta-label">Total</div>
                                <div class="order-meta-val">PKR {{ number_format($order->total_amount) }}</div>
                            </div>
                            <div>
                                <div class="order-meta-label">Ship To</div>
                                <div class="order-meta-val pink">{{ $order->shipping_name }}</div>
                            </div>
                        </div>
                        <div class="text-sm-end">
                            <div class="order-meta-label">Tracking Number</div>
                            <div class="order-meta-val">{{ $order->tracking_number }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="status-badge {{ $st['class'] }} me-2">
                            <i class="{{ $st['icon'] }}"></i> {{ strtoupper($st['label']) }}
                        </span>
                    </div>

                    @php $itemAwaitingStock = $order->order_type === 'pre_order' && $order->order_status === 'awaiting_stock'; @endphp
                    @php $itemPaymentDue    = $order->order_type === 'pre_order' && $order->order_status === 'payment_due'; @endphp
                    @foreach($order->items as $item)
                    @php $refund = $item->refundRequest; $rst = $refund ? ($refundStatusMap[$refund->status] ?? $refundStatusMap['pending']) : null; @endphp
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img src="{{ $item->product?->images?->first()?->image ?? 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=100&q=80' }}" alt="{{ $item->product?->name }}" class="order-item-img">
                        <div class="flex-grow-1">
                            <div class="order-item-name">{{ $item->product?->name }}</div>
                            <div class="order-item-sub">Qty: {{ $item->qty }}{{ $item->productVariant ? ' · '.$item->productVariant->variant_name : '' }}</div>
                            <div class="order-item-price">PKR {{ number_format($item->price * $item->qty) }}</div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            @if($itemAwaitingStock)
                            <span class="status-badge status-awaiting_stock"><i class="ri-hourglass-line"></i> Awaiting Stock</span>
                            @elseif($itemPaymentDue)
                            <button type="button" class="pay-remaining-item-btn"
                                onclick="payRemainingItem({{ Js::from($order->uuid) }}, this)">Complete Remaining Payment</button>
                            @endif
                            @if(!$refund && $item->isRefundEligible())
                            <button type="button" class="refund-request-btn" style="margin-left:0;"
                                onclick="openRefundModal({{ $item->id }}, {{ $item->price * $item->qty }}, null, '', '')">Request Refund</button>
                            @endif
                            @if($item->review)
                            <span class="status-badge status-delivered"><i class="ri-checkbox-circle-line"></i> Review Submitted</span>
                            @elseif($item->canBeReviewed())
                            <button type="button" class="refund-request-btn" style="margin-left:0;"
                                onclick="openReviewModal({{ $item->id }}, @js($item->product?->name))">Write Review</button>
                            @endif
                        </div>
                    </div>

                    @if($refund)
                    <div class="refund-card">
                        <div class="refund-card-row"><span class="refund-card-label">Status</span><span class="status-badge {{ $rst['class'] }}">{{ $rst['label'] }}</span></div>
                        <div class="refund-card-row"><span class="refund-card-label">Requested Date</span><span class="refund-card-val">{{ $refund->requested_at?->format('d/m/Y') }}</span></div>
                        <div class="refund-card-row"><span class="refund-card-label">Refund Amount</span><span class="refund-card-val">PKR {{ number_format($refund->amount) }}</span></div>
                        <div class="refund-card-row"><span class="refund-card-label">Bank Details</span><span class="refund-card-val">{{ $refund->bank_details }}</span></div>
                        <div class="refund-card-row"><span class="refund-card-label">Reason</span><span class="refund-card-val">{{ $refund->reason }}</span></div>
                        @if($refund->status === 'pending')
                        <div class="refund-actions">
                            <button type="button" class="refund-request-btn" style="margin-left:0;"
                                onclick="openRefundModal({{ $item->id }}, {{ $refund->amount }}, {{ $refund->id }}, @js($refund->bank_details), @js($refund->reason))">Edit</button>
                            <button type="button" class="refund-cancel-btn" onclick="cancelRefundRequest({{ $refund->id }})">Cancel Request</button>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <a href="{{ route('orders.show', $order) }}" class="track-btn text-decoration-none">Order Details</a>
                        <button type="button" class="track-btn"
                            onclick="window.location.href='{{ route('track-order', ['tracking_number' => $order->tracking_number]) }}'">Track Package</button>
                    </div>
                </div>
                @empty
                <p class="text-muted" style="font-size:.85rem;">No orders yet.</p>
                @endforelse
            </div>
        </div>

        {{-- ── Addresses ── --}}
        <div id="tab-addresses" class="tab-panel">
            <div class="content-card">
                <div class="section-heading">Shipping Addresses</div>
                <div class="section-sub">Manage your shipping and billing addresses</div>

                <div class="row g-3">
                    @foreach($addresses as $addr)
                        <div class="col-md-6">
                            <div class="address-card {{ $addr->isDefault ? 'default' : '' }}">
                                @if($addr->isDefault)
                                    <span class="default-badge">Default</span>
                                @endif
                                <div class="fw-bold mb-1">{{ $addr->name }}</div>
                                <div class="text-muted small mb-1">{{ $addr->address }}</div>
                                <div class="text-muted small mb-1">{{ collect([$addr->city, $addr->country])->filter()->implode(', ') }}</div>
                                <div class="text-muted small mb-3">{{ $addr->phone }}</div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="invoice-btn" style="padding:.3rem .9rem;font-size:.78rem;"
                                            onclick='openAddressModal(@json($addr->toArray()))'>Edit</button>
                                    <form id="delete-address-form-{{ $addr->id }}" action="{{ route('addresses.destroy', $addr->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="invoice-btn" style="padding:.3rem .9rem;font-size:.78rem;color:#ef4444;border-color:#fecaca;"
                                            onclick="openConfirmDialog('Are you sure you want to delete this address?', () => document.getElementById('delete-address-form-{{ $addr->id }}').submit())">Delete</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-6">
                        <div class="add-address-card" onclick="openAddressModal(null)">
                            <i class="ri-map-pin-add-line" style="font-size:1.75rem;margin-bottom:.5rem;"></i>
                            <span style="font-size:.875rem;font-weight:500;">Add New Address</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Settings ── --}}
        <div id="tab-settings" class="tab-panel">
            <div class="content-card">
                <div class="section-heading">Account Settings</div>
                <div class="section-sub">Update your password and preferences</div>

                {{-- Personal Information --}}
                <div class="settings-section-title">
                    <i class="ri-user-settings-line"></i> Personal Information
                </div>
                <form id="updateProfileForm" action="{{ route('profile.edit') }}" method="POST" class="mb-5" style="max-width:36rem;">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.8rem;font-weight:600;">Full Name</label>
                            <input type="text" name="name" id="profileNameInput" class="form-input" value="{{ old('name', $profile->name) }}">
                            <div id="profileNameError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;">
                                @error('name', 'updateProfile')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.8rem;font-weight:600;">Email Address</label>
                            <input type="email" name="email" id="profileEmailInput" class="form-input" value="{{ old('email', $profile->email) }}">
                            <div id="profileEmailError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;">
                                @error('email', 'updateProfile')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-6">
                            <label class="form-label"  style="font-size:.8rem;font-weight:600;">Phone Number</label>
                            <input type="tel" name="phone_number" id="profilePhoneInput" class="form-input" placeholder="+923001234567" value="{{ old('phone_number', $profile->phoneNumber) }}">
                            <div id="profilePhoneError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;">
                                @error('phone_number', 'updateProfile')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div id="updateProfileSuccess" class="text-success mb-2" style="font-size:.8rem;"></div>
                    <button type="button" class="update-btn" onclick="submitUpdateProfileForm()">Save Changes</button>
                </form>

                {{-- Security --}}
                <div class="settings-section-title">
                    <i class="ri-shield-keyhole-line"></i> Security
                </div>
                <form id="updatePasswordForm" action="{{ route('profile.password') }}" method="POST" class="mb-5">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.8rem;font-weight:600;">Current Password</label>
                        <input type="password" name="current_password" id="currentPasswordInput" class="form-input" style="max-width:26rem;">
                        <div id="currentPasswordError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;">
                            @error('current_password', 'updatePassword')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-2" style="max-width:36rem;">
                        <div class="col-6">
                            <label class="form-label" style="font-size:.8rem;font-weight:600;">New Password</label>
                            <input type="password" name="password" id="newPasswordInput" class="form-input" placeholder="Enter new password">
                            <div id="newPasswordError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;">
                                @error('password', 'updatePassword')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:.8rem;font-weight:600;">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="confirmPasswordInput" class="form-input" placeholder="Confirm new password">
                        </div>
                    </div>
                    <div id="updatePasswordSuccess" class="text-success mb-2" style="font-size:.8rem;"></div>
                    <button type="button" class="update-btn" onclick="submitUpdatePasswordForm()">Update Password</button>
                </form>

                {{-- Danger Zone --}}
                <div class="settings-section-title mt-5">
                    <i class="ri-error-warning-line"></i> Danger Zone
                </div>
                <div class="danger-zone">
                    <div class="fw-bold mb-1" style="font-size:.9rem;">Delete Account</div>
                    <p class="text-muted mb-3" style="font-size:.8rem;">
                        This will permanently delete your account and all associated data. This action cannot be undone.
                    </p>
                    <form id="deleteAccountForm" action="{{ route('profile.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex gap-2 align-items-end flex-wrap">
                            <div>
                                <label class="form-label" style="font-size:.8rem;font-weight:600;">Confirm Password</label>
                                <input type="password" name="password" id="deleteAccountPassword" class="form-input" placeholder="Enter your password" style="max-width:16rem;" required>
                            </div>
                            <button type="button" class="danger-btn" onclick="confirmDeleteAccount()">Delete My Account</button>
                        </div>
                        <div id="deleteAccountError" class="text-danger" style="font-size:.75rem;margin-top:.5rem;">
                            @error('password', 'deleteAccount')
                                {{ $message }}
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
</div>

{{-- ── Add / Edit Address Modal ── --}}
<div class="address-modal-overlay" id="addressModalOverlay" onclick="if(event.target===this) closeAddressModal()">
    <div class="address-modal-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" id="addressModalTitle">Add New Address</h5>
            <button type="button" class="address-modal-close" onclick="closeAddressModal()">&times;</button>
        </div>
        <form id="addressForm" method="POST" action="{{ route('addresses.store') }}">
            @csrf
            <input type="hidden" name="_method" id="addressFormMethod" value="POST">
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Full Name</label>
                <input type="text" name="name" id="addressName" class="form-input" required>
                <div id="addressNameError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Address</label>
                <input type="text" name="address" id="addressLine" class="form-input" required>
                <div id="addressLineError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;"></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">City</label>
                    <input type="text" name="city" id="addressCity" class="form-input" required>
                    <div id="addressCityError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;"></div>
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Country</label>
                    <input type="text" name="country" id="addressCountry" class="form-input" required>
                    <div id="addressCountryError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;"></div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600;">Phone</label>
                <input type="tel" name="phone" id="addressPhone" class="form-input" placeholder="+923001234567">
                <div id="addressPhoneError" class="text-danger" style="font-size:.75rem;margin-top:.3rem;"></div>
            </div>
            <div class="mb-4 d-flex align-items-center gap-2">
                <input type="checkbox" name="is_default" id="addressIsDefault" value="1" style="width:1.1rem;height:1.1rem;cursor:pointer;">
                <label for="addressIsDefault" class="mb-0" style="font-size:.85rem;">Set as default address</label>
            </div>
            <button type="button" class="update-btn w-100" onclick="submitAddressForm()">Save Address</button>
        </form>
    </div>
</div>

{{-- ── Confirm Dialog ── --}}
<div class="address-modal-overlay" id="confirmDialogOverlay" onclick="if(event.target===this) closeConfirmDialog()">
    <div class="address-modal-box" style="max-width:22rem;text-align:center;">
        <div class="mb-3" style="font-size:2.25rem;color:#ef4444;">
            <i class="ri-error-warning-line"></i>
        </div>
        <h5 class="fw-bold mb-2">Are you sure?</h5>
        <p class="text-muted mb-4" id="confirmDialogMessage" style="font-size:.875rem;"></p>
        <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="invoice-btn" style="padding:.55rem 1.4rem;" onclick="closeConfirmDialog()">Cancel</button>
            <button type="button" class="danger-btn" id="confirmDialogConfirmBtn">Delete</button>
        </div>
    </div>
</div>

{{-- ── Refund Request Modal ── --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalTitle">Request Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="refundOrderItemId">
                <input type="hidden" id="refundId">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Refund Amount</label>
                    <input type="text" class="form-control" id="refundAmount" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Bank Details</label>
                    <textarea class="form-control" id="refundBankDetails" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Reason</label>
                    <textarea class="form-control" id="refundReason" rows="3" required></textarea>
                </div>
                <div id="refundFormError" class="text-danger" style="font-size:.75rem;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="update-btn" onclick="submitRefundRequest()">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Write Review Modal ── --}}
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalTitle">Write Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reviewOrderItemId">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Rating</label>
                    <div id="reviewStarPicker">
                        @for($s=1;$s<=5;$s++)
                        <i class="ri-star-line" data-star="{{ $s }}" onclick="setReviewRating({{ $s }})"
                           style="font-size:1.5rem;color:#f59e0b;cursor:pointer;"></i>
                        @endfor
                    </div>
                    <input type="hidden" id="reviewRating" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Comment (optional)</label>
                    <textarea class="form-control" id="reviewComment" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:.8rem;font-weight:600;">Photos (optional, up to 5, max 1MB each)</label>
                    <div id="reviewImagePreviews" class="d-flex flex-wrap gap-2 mb-2"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="reviewAddImageBtn"
                            onclick="document.getElementById('reviewImageInput').click()">
                        <i class="ri-add-line"></i> Add Photo
                    </button>
                    <input type="file" id="reviewImageInput" accept="image/*" class="d-none" onchange="addReviewImage(this)">
                </div>
                <div id="reviewFormError" class="text-danger" style="font-size:.75rem;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="update-btn" onclick="submitReviewRequest()">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const requestedTab = new URLSearchParams(window.location.search).get('tab');
    if (requestedTab && document.getElementById('tab-' + requestedTab)) {
        const btn = Array.from(document.querySelectorAll('.profile-tab'))
            .find(b => b.getAttribute('onclick')?.includes("'" + requestedTab + "'"));
        if (btn) switchTab(requestedTab, btn);
    }
});

const addressStoreUrl = "{{ route('addresses.store') }}";

function openAddressModal(address) {
    const form = document.getElementById('addressForm');

    document.getElementById('addressNameError').textContent = '';
    document.getElementById('addressLineError').textContent = '';
    document.getElementById('addressCityError').textContent = '';
    document.getElementById('addressCountryError').textContent = '';
    document.getElementById('addressPhoneError').textContent = '';

    if (address) {
        document.getElementById('addressModalTitle').textContent = 'Edit Address';
        document.getElementById('addressFormMethod').value = 'PUT';
        form.action = addressStoreUrl + '/' + address.id;
        document.getElementById('addressName').value = address.name || '';
        document.getElementById('addressLine').value = address.address || '';
        document.getElementById('addressCity').value = address.city || '';
        document.getElementById('addressCountry').value = address.country || '';
        document.getElementById('addressPhone').value = address.phone || '';
        document.getElementById('addressIsDefault').checked = !!address.is_default;
    } else {
        document.getElementById('addressModalTitle').textContent = 'Add New Address';
        document.getElementById('addressFormMethod').value = 'POST';
        form.action = addressStoreUrl;
        form.reset();
    }

    document.getElementById('addressModalOverlay').classList.add('open');
}

function closeAddressModal() {
    document.getElementById('addressModalOverlay').classList.remove('open');
}

function submitAddressForm() {
    const form = document.getElementById('addressForm');
    const method = document.getElementById('addressFormMethod').value;
    const nameError = document.getElementById('addressNameError');
    const lineError = document.getElementById('addressLineError');
    const cityError = document.getElementById('addressCityError');
    const countryError = document.getElementById('addressCountryError');
    const phoneError = document.getElementById('addressPhoneError');

    nameError.textContent = '';
    lineError.textContent = '';
    cityError.textContent = '';
    countryError.textContent = '';
    phoneError.textContent = '';

    fetch(form.action, {
        method: method,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
        },
        body: new FormData(form),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            window.location.href = data.redirect || window.location.pathname + '?tab=addresses';
            return;
        }

        const errors = data.errors || {};
        if (errors.name) nameError.textContent = errors.name[0];
        if (errors.address) lineError.textContent = errors.address[0];
        if (errors.city) cityError.textContent = errors.city[0];
        if (errors.country) countryError.textContent = errors.country[0];
        if (errors.phone) phoneError.textContent = errors.phone[0];
        if (!errors.name && !errors.address && !errors.city && !errors.country && !errors.phone) {
            phoneError.textContent = data.message || 'Something went wrong. Please try again.';
        }
    })
    .catch(() => {
        phoneError.textContent = 'Something went wrong. Please try again.';
    });
}

let confirmDialogAction = null;

function openConfirmDialog(message, onConfirm) {
    confirmDialogAction = onConfirm;
    document.getElementById('confirmDialogMessage').textContent = message;
    document.getElementById('confirmDialogOverlay').classList.add('open');
}

function closeConfirmDialog() {
    document.getElementById('confirmDialogOverlay').classList.remove('open');
    confirmDialogAction = null;
}

document.getElementById('confirmDialogConfirmBtn').addEventListener('click', () => {
    const action = confirmDialogAction;
    closeConfirmDialog();
    if (action) {
        action();
    }
});

function confirmDeleteAccount() {
    openConfirmDialog(
        'Are you sure you want to permanently delete your account? This action cannot be undone.',
        submitDeleteAccountForm
    );
}

function submitDeleteAccountForm() {
    const form = document.getElementById('deleteAccountForm');
    const errorEl = document.getElementById('deleteAccountError');
    errorEl.textContent = '';

    fetch(form.action, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
        },
        body: new FormData(form),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            window.location.href = data.redirect || '{{ route('login') }}';
            return;
        }

        const message = (data.errors && data.errors.password && data.errors.password[0])
            || data.message
            || 'Something went wrong. Please try again.';
        errorEl.textContent = message;
        document.getElementById('deleteAccountPassword').value = '';
    })
    .catch(() => {
        errorEl.textContent = 'Something went wrong. Please try again.';
    });
}

function submitUpdateProfileForm() {
    const form = document.getElementById('updateProfileForm');
    const nameError = document.getElementById('profileNameError');
    const emailError = document.getElementById('profileEmailError');
    const phoneError = document.getElementById('profilePhoneError');
    const successEl = document.getElementById('updateProfileSuccess');

    nameError.textContent = '';
    emailError.textContent = '';
    phoneError.textContent = '';
    successEl.textContent = '';

    fetch(form.action, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
        },
        body: new FormData(form),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            successEl.textContent = data.message || 'Profile updated successfully.';
            return;
        }

        const errors = data.errors || {};
        if (errors.name) nameError.textContent = errors.name[0];
        if (errors.email) emailError.textContent = errors.email[0];
        if (errors.phone_number) phoneError.textContent = errors.phone_number[0];
        if (!errors.name && !errors.email && !errors.phone_number) {
            phoneError.textContent = data.message || 'Something went wrong. Please try again.';
        }
    })
    .catch(() => {
        phoneError.textContent = 'Something went wrong. Please try again.';
    });
}

function submitUpdatePasswordForm() {
    const form = document.getElementById('updatePasswordForm');
    const currentPasswordError = document.getElementById('currentPasswordError');
    const newPasswordError = document.getElementById('newPasswordError');
    const successEl = document.getElementById('updatePasswordSuccess');

    currentPasswordError.textContent = '';
    newPasswordError.textContent = '';
    successEl.textContent = '';

    fetch(form.action, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
        },
        body: new FormData(form),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));

        if (response.ok) {
            successEl.textContent = data.message || 'Password updated successfully.';
            document.getElementById('currentPasswordInput').value = '';
            document.getElementById('newPasswordInput').value = '';
            document.getElementById('confirmPasswordInput').value = '';
            return;
        }

        const errors = data.errors || {};
        if (errors.current_password) currentPasswordError.textContent = errors.current_password[0];
        if (errors.password) newPasswordError.textContent = errors.password[0];
        if (!errors.current_password && !errors.password) {
            newPasswordError.textContent = data.message || 'Something went wrong. Please try again.';
        }
    })
    .catch(() => {
        newPasswordError.textContent = 'Something went wrong. Please try again.';
    });
}

let refundModalInstance;
function openRefundModal(orderItemId, amount, refundId, bankDetails, reason) {
    document.getElementById('refundOrderItemId').value = orderItemId;
    document.getElementById('refundId').value = refundId || '';
    document.getElementById('refundAmount').value = amount;
    document.getElementById('refundBankDetails').value = bankDetails || '';
    document.getElementById('refundReason').value = reason || '';
    document.getElementById('refundModalTitle').textContent = refundId ? 'Edit Refund Request' : 'Request Refund';
    document.getElementById('refundFormError').textContent = '';
    refundModalInstance = refundModalInstance || new bootstrap.Modal(document.getElementById('refundModal'));
    refundModalInstance.show();
}

function submitRefundRequest() {
    const refundId       = document.getElementById('refundId').value;
    const orderItemId    = document.getElementById('refundOrderItemId').value;
    const amount         = document.getElementById('refundAmount').value;
    const bankDetails    = document.getElementById('refundBankDetails').value;
    const reason         = document.getElementById('refundReason').value;
    const errorEl        = document.getElementById('refundFormError');

    const url    = refundId ? `/refund-requests/${refundId}` : `{{ route('refund-requests.store') }}`;
    const method = refundId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ order_item_id: orderItemId, amount: amount, bank_details: bankDetails, reason: reason }),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (response.ok) {
            window.location.href = window.location.pathname + '?tab=orders';
            return;
        }
        errorEl.textContent = data.message || 'Something went wrong. Please try again.';
    })
    .catch(() => {
        errorEl.textContent = 'Something went wrong. Please try again.';
    });
}

function payRemainingItem(orderUuid, btn) {
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.textContent = 'Processing…';

    fetch(`/orders/${orderUuid}/pay-remaining`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (response.ok) {
            window.location.href = window.location.pathname + '?tab=orders';
            return;
        }
        alert(data.message || 'Payment failed. Please try again.');
        btn.disabled = false;
        btn.textContent = originalText;
    })
    .catch(() => {
        alert('Something went wrong. Please try again.');
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

let reviewModalInstance;

function setReviewRating(rating) {
    document.getElementById('reviewRating').value = rating;
    document.querySelectorAll('#reviewStarPicker i').forEach(star => {
        const isFilled = Number(star.getAttribute('data-star')) <= rating;
        star.className = isFilled ? 'ri-star-fill' : 'ri-star-line';
    });
}

let reviewSelectedImages = [];

function renderReviewImagePreviews() {
    const container = document.getElementById('reviewImagePreviews');
    container.innerHTML = reviewSelectedImages.map((file, idx) => `
        <div style="position:relative;width:3.5rem;height:3.5rem;">
            <img src="${URL.createObjectURL(file)}" style="width:100%;height:100%;object-fit:cover;border-radius:.4rem;border:1px solid #e5e7eb;">
            <button type="button" onclick="removeReviewImage(${idx})"
                    style="position:absolute;top:-.4rem;right:-.4rem;width:1.2rem;height:1.2rem;border-radius:50%;background:#ef4444;color:#fff;border:0;font-size:.7rem;line-height:1;padding:0;">×</button>
        </div>
    `).join('');

    document.getElementById('reviewAddImageBtn').style.display = reviewSelectedImages.length >= 5 ? 'none' : '';
}

function addReviewImage(input) {
    const errorEl = document.getElementById('reviewFormError');
    const file = input.files[0];
    input.value = '';

    if (!file) return;

    if (reviewSelectedImages.length >= 5) {
        errorEl.textContent = 'You can upload a maximum of 5 photos.';
        return;
    }
    if (file.size > 1024 * 1024) {
        errorEl.textContent = `"${file.name}" is larger than 1MB. Please choose a smaller image.`;
        return;
    }

    errorEl.textContent = '';
    reviewSelectedImages.push(file);
    renderReviewImagePreviews();
}

function removeReviewImage(idx) {
    reviewSelectedImages.splice(idx, 1);
    renderReviewImagePreviews();
}

function openReviewModal(orderItemId, productName) {
    document.getElementById('reviewOrderItemId').value = orderItemId;
    document.getElementById('reviewComment').value = '';
    document.getElementById('reviewFormError').textContent = '';
    document.getElementById('reviewModalTitle').textContent = 'Write Review' + (productName ? ' — ' + productName : '');
    setReviewRating(0);
    reviewSelectedImages = [];
    renderReviewImagePreviews();
    reviewModalInstance = reviewModalInstance || new bootstrap.Modal(document.getElementById('reviewModal'));
    reviewModalInstance.show();
}

function submitReviewRequest() {
    const orderItemId = document.getElementById('reviewOrderItemId').value;
    const rating       = document.getElementById('reviewRating').value;
    const comments     = document.getElementById('reviewComment').value;
    const errorEl      = document.getElementById('reviewFormError');

    if (Number(rating) < 1) {
        errorEl.textContent = 'Please select a rating.';
        return;
    }

    const formData = new FormData();
    formData.append('order_item_id', orderItemId);
    formData.append('rating', rating);
    if (comments) formData.append('comments', comments);
    reviewSelectedImages.forEach(file => formData.append('images[]', file));

    fetch('{{ route('reviews.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (response.ok) {
            window.location.href = window.location.pathname + '?tab=orders';
            return;
        }
        errorEl.textContent = data.message || 'Something went wrong. Please try again.';
    })
    .catch(() => {
        errorEl.textContent = 'Something went wrong. Please try again.';
    });
}

function cancelRefundRequest(refundId) {
    if (!confirm('Cancel this refund request?')) return;

    fetch(`/refund-requests/${refundId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(async (response) => {
        if (response.ok) {
            window.location.href = window.location.pathname + '?tab=orders';
            return;
        }
        const data = await response.json().catch(() => ({}));
        alert(data.message || 'Something went wrong. Please try again.');
    })
    .catch(() => alert('Something went wrong. Please try again.'));
}
</script>
@endsection
