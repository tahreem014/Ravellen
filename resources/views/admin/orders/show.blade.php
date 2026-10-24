@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="mb-3">
        <a href="{{ $order->isPreOrder() ? route('admin.pre-orders.index') : route('admin.orders.index') }}" class="text-decoration-none">
            <i class="ri-arrow-left-line"></i> Back to {{ $order->isPreOrder() ? 'Pre-Orders' : 'Orders' }}
        </a>
    </div>

    {{-- data-admin-region / data-ajax: the status form saves in place (layouts/admin) --}}
    <div class="row g-4" data-admin-region="order">
        <div class="col-lg-8">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Items</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product?->name }}</td>
                                        <td>{{ $item->productVariant?->variant_name }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>PKR {{ number_format($item->price) }}</td>
                                        <td>PKR {{ number_format($item->price * $item->qty) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="mb-1">Update Status</h5>
                    <p class="text-muted small mb-3">The customer sees the new status in their account (Order Details, My Orders, Track Order). No email is sent.</p>
                    <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="row g-3" data-ajax>
                        @csrf @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label">Order Status</label>
                            <select name="order_status" class="form-select">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ \App\Models\Order::STATUS_LABELS[$status] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order Reference</label>
                            <div class="form-control-plaintext">{{ $order->tracking_number ?? '—' }}</div>
                            <div class="form-text">Customers can search this on Track Order.</div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-admin-primary" type="submit">Save</button>
                        </div>
                    </form>
                    @if ($order->delivered_at)
                        <p class="text-muted small mt-3 mb-0">Delivered {{ $order->delivered_at->format('d M Y, g:i a') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Customer</h5>
                    <p class="mb-1">{{ $order->user?->name }}</p>
                    <p class="mb-1 text-muted">{{ $order->user?->email }}</p>
                    <p class="mb-0 text-muted">{{ $order->shipping_phone }}</p>
                </div>
            </div>

            <div class="admin-card card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Shipping</h5>
                    <p class="mb-1">{{ $order->shipping_name }}</p>
                    <p class="mb-0 text-muted">{{ $order->shipping_address }}</p>
                </div>
            </div>

            <div class="admin-card card">
                <div class="card-body">
                    <h5 class="mb-3">Payment</h5>
                    <p class="mb-1">Method: {{ $order->payment_method_label }}</p>
                    <p class="mb-1">Status: {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</p>
                    @if ($order->payment_reference)
                        <p class="mb-1 text-muted small">Safepay ref: {{ $order->payment_reference }}</p>
                    @endif
                    @if ($order->paid_at)
                        <p class="mb-1 text-muted small">Paid: {{ $order->paid_at->format('d M Y H:i') }}</p>
                    @endif
                    @if ($order->isPreOrder())
                        <hr class="my-2">
                        <p class="mb-1">Advance: PKR {{ number_format($order->advance_amount) }}
                            @if ($order->payment_stage === \App\Models\Order::STAGE_ADVANCE_PAID || $order->payment_stage === \App\Models\Order::STAGE_COMPLETED)
                                <span class="badge bg-success">paid</span>
                            @else
                                <span class="badge bg-secondary">pending</span>
                            @endif
                        </p>
                        <p class="mb-1">Remaining: PKR {{ number_format($order->remaining_amount) }}
                            @if ($order->payment_stage === \App\Models\Order::STAGE_COMPLETED)
                                <span class="badge bg-success">paid</span>
                            @else
                                <span class="badge bg-secondary">pending</span>
                            @endif
                        </p>
                        @if ($order->remaining_payment_reference)
                            <p class="mb-1 text-muted small">Remaining Safepay ref: {{ $order->remaining_payment_reference }}</p>
                        @endif
                        @if ($order->remaining_paid_at)
                            <p class="mb-1 text-muted small">Remaining paid: {{ $order->remaining_paid_at->format('d M Y H:i') }}</p>
                        @endif
                    @endif
                    <p class="mb-0 fw-bold mt-2">Total: PKR {{ number_format($order->total_amount) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
