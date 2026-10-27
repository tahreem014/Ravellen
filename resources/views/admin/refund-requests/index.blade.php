@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0">{{ $title }}</h5>
        <form method="GET" class="admin-toolbar-form">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- data-admin-region / data-ajax: approve / reject save in place (layouts/admin) --}}
    <div class="admin-card card" data-admin-region="refunds">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Item</th>
                        <th>Amount (PKR)</th>
                        <th>Bank Details</th>
                        <th>Reason</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refundRequests as $refund)
                        <tr>
                            <td data-label="ID">{{ $refund->id }}</td>
                            <td data-label="Customer">{{ $refund->orderItem?->order?->user?->name ?? '—' }}</td>
                            <td data-label="Item">{{ $refund->orderItem?->product?->name ?? '—' }}</td>
                            <td data-label="Amount (PKR)">{{ number_format($refund->amount) }}</td>
                            <td class="stack-block" data-label="Bank Details">{{ $refund->bank_details }}</td>
                            <td class="stack-block" data-label="Reason">{{ $refund->reason }}</td>
                            <td data-label="Requested">{{ $refund->requested_at?->format('M j, Y') }}</td>
                            <td data-label="Status">
                                <span class="badge bg-{{ match($refund->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'warning',
                                } }}">{{ ucfirst($refund->status) }}</span>
                            </td>
                            {{-- nothing to act on once decided: on a phone that cell (just a dash) is hidden --}}
                            <td class="text-end stack-actions {{ $refund->status === 'pending' ? '' : 'd-none d-md-table-cell' }}">
                                @if ($refund->status === 'pending')
                                    <form action="{{ route('admin.refund-requests.update', $refund) }}" method="POST" class="d-inline" data-ajax>
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-outline-success"><i class="ri-check-line"></i> Approve</button>
                                    </form>
                                    <form action="{{ route('admin.refund-requests.update', $refund) }}" method="POST" class="d-inline" data-ajax>
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-sm btn-outline-danger"><i class="ri-close-line"></i> Reject</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No refund requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $refundRequests->links() }}</div>
@endsection
