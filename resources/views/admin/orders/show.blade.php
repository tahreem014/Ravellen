@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
    <div class="mb-3">
        <a href="{{ $order->isPreOrder() ? route('admin.pre-orders.index') : route('admin.orders.index') }}" class="text-decoration-none">
            <i class="ri-arrow-left-line"></i> Back to {{ $order->isPreOrder() ? 'Pre-Orders' : 'Orders' }}
        </a>
    </div>

    <div class="row g-4">
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
                    <h5 class="mb-3">Update Status</h5>
                    <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label">Order Status</label>
                            <select name="order_status" class="form-select">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($order->order_status === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tracking Number</label>
                            <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-admin-primary" type="submit">Save</button>
                        </div>
                    </form>
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
                    <p class="mb-1">Method: {{ ucfirst($order->payment_method) }}</p>
                    <p class="mb-1">Status: {{ ucfirst($order->payment_status) }}</p>
                    @if ($order->isPreOrder())
                        <p class="mb-1">Advance: PKR {{ number_format($order->advance_amount) }}</p>
                        <p class="mb-1">Remaining: PKR {{ number_format($order->remaining_amount) }}</p>
                    @endif
                    <p class="mb-0 fw-bold">Total: PKR {{ number_format($order->total_amount) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
