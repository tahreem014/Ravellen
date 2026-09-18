@extends('layouts.admin')

@section('title', 'Shipping Rates')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div></div>
        <button class="btn btn-admin-primary btn-sm" onclick="openShippingRateModal(null)">
            <i class="ri-add-line"></i> Add Shipping Rate
        </button>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>City</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shippingRates as $rate)
                        <tr>
                            <td>{{ $rate->country }}</td>
                            <td>{{ $rate->city ?? 'All cities (country-wide)' }}</td>
                            <td>PKR {{ number_format($rate->amount, 2) }}</td>
                            <td>
                                @if ($rate->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick='openShippingRateModal(@json($rate))'>Edit</button>

                                <form action="{{ route('admin.shipping-rates.toggle-active', $rate) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">{{ $rate->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>

                                <form action="{{ route('admin.shipping-rates.destroy', $rate) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this shipping rate? Past orders keep their recorded shipping fee.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No shipping rates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $shippingRates->links() }}</div>

    <div class="modal fade" id="shippingRateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="shippingRateForm" method="POST" action="{{ route('admin.shipping-rates.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="shippingRateFormMethod" value="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shippingRateModalTitle">Add Shipping Rate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger small">{{ $errors->first() }}</div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="shippingRateCountry" class="form-control" value="{{ old('country') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" id="shippingRateCity" class="form-control" value="{{ old('city') }}" placeholder="Leave blank for a country-wide rate">
                            <div class="form-text">Leave blank to apply this rate to every city in the country not covered by a more specific rate.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" step="0.01" min="0" name="amount" id="shippingRateAmount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="shippingRateIsActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="shippingRateIsActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-admin-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const shippingRateStoreUrl = "{{ route('admin.shipping-rates.store') }}";
const shippingRateModalEl = document.getElementById('shippingRateModal');
let shippingRateModal;
document.addEventListener('DOMContentLoaded', () => {
    shippingRateModal = new bootstrap.Modal(shippingRateModalEl);
    @if ($errors->any())
        shippingRateModal.show();
    @endif
});

function openShippingRateModal(rate) {
    const form = document.getElementById('shippingRateForm');

    if (rate) {
        document.getElementById('shippingRateModalTitle').textContent = 'Edit Shipping Rate';
        document.getElementById('shippingRateFormMethod').value = 'PUT';
        form.action = shippingRateStoreUrl + '/' + rate.id;
        document.getElementById('shippingRateCountry').value = rate.country || '';
        document.getElementById('shippingRateCity').value = rate.city || '';
        document.getElementById('shippingRateAmount').value = rate.amount || '';
        document.getElementById('shippingRateIsActive').checked = !!rate.is_active;
    } else {
        document.getElementById('shippingRateModalTitle').textContent = 'Add Shipping Rate';
        document.getElementById('shippingRateFormMethod').value = 'POST';
        form.action = shippingRateStoreUrl;
        form.reset();
        document.getElementById('shippingRateIsActive').checked = true;
    }

    shippingRateModal.show();
}
</script>
@endsection
