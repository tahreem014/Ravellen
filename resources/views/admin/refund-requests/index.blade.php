@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="mb-0">{{ $title }}</h5>
        <form method="GET" class="d-flex gap-2">
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
            <table class="table align-middle mb-0">
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
                            <td>{{ $refund->id }}</td>
                            <td>{{ $refund->orderItem?->order?->user?->name ?? '—' }}</td>
                            <td>{{ $refund->orderItem?->product?->name ?? '—' }}</td>
                            <td>{{ number_format($refund->amount) }}</td>
                            <td>{{ $refund->bank_details }}</td>
                            <td>{{ $refund->reason }}</td>
                            <td>{{ $refund->requested_at?->format('M j, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ match($refund->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'warning',
                                } }}">{{ ucfirst($refund->status) }}</span>
                            </td>
                            <td class="text-end">
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
