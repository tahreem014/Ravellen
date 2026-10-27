@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0">{{ $title }}</h5>
        <form method="GET" class="admin-toolbar-form">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Models\Order::STATUS_LABELS[$status] }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Customer</th>
                        <th>Advance</th>
                        <th>Remaining</th>
                        <th>Stage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="stack-title">{{ $order->tracking_number ?? '—' }}</td>
                            <td data-label="Customer">{{ $order->user?->name }}</td>
                            <td data-label="Advance">PKR {{ number_format($order->advance_amount) }}</td>
                            <td data-label="Remaining">PKR {{ number_format($order->remaining_amount) }}</td>
                            <td data-label="Stage">{{ $order->payment_stage ? ucfirst(str_replace('_',' ', $order->payment_stage)) : '—' }}</td>
                            <td data-label="Status"><span class="badge bg-secondary">{{ $order->status_label }}</span></td>
                            <td class="text-end stack-actions">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-eye-line"></i> View</a>
                                @if ($order->order_status === \App\Models\Order::STATUS_PAYMENT_DUE)
                                    <form action="{{ route('admin.pre-orders.collect-remaining', $order) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Mark the remaining payment as collected?', 'btn-admin-primary');">
                                        @csrf
                                        <button class="btn btn-sm btn-admin-primary">Collect Remaining</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No pre-orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
