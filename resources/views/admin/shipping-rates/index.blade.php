@extends('layouts.admin')

@section('title', 'Shipping Rates')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" action="{{ route('admin.shipping-rates.index') }}" class="d-flex gap-2 flex-grow-1" style="max-width:26rem;">
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search country or city">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.shipping-rates.index') }}" class="btn btn-sm btn-link">Clear</a>
            @endif
        </form>
        <button class="btn btn-admin-primary btn-sm admin-toolbar-btn" onclick="openShippingRateModal(null)">
            <i class="ri-add-line"></i> Add Shipping Rate
        </button>
    </div>

    <div class="alert alert-light border small">
        <strong>How the delivery charge is chosen:</strong> a rate for the customer's city wins; otherwise the country's
        default rate applies; if there is neither, we don't deliver there and checkout is blocked. Add a country's default
        rate once, then add city rates only where a city costs something different.
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Applies to</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shippingRates as $rate)
                        <tr>
                            <td class="stack-title">{{ $rate->country }}</td>
                            <td data-label="Applies to">
                                @if ($rate->isCountryWide())
                                    <span class="badge bg-primary">Country default</span>
                                    <span class="text-muted small d-block">every city without its own rate</span>
                                @else
                                    {{ $rate->city }}
                                @endif
                            </td>
                            <td data-label="Amount">PKR {{ number_format($rate->amount, 2) }}</td>
                            <td data-label="Status">
                                @if ($rate->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap stack-actions">
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
                        <tr><td colspan="5" class="text-center text-muted py-4">
                            {{ $search !== '' ? 'No shipping rates match your search.' : 'No shipping rates yet. Add a country\'s default rate to start delivering there.' }}
                        </td></tr>
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
                    <input type="hidden" name="editing_id" id="shippingRateEditingId" value="{{ old('editing_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shippingRateModalTitle">Add Shipping Rate</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger small">{{ $errors->first() }}</div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label" for="shippingRateCountry">Country</label>
                            <select name="country" id="shippingRateCountry" class="form-select" required>
                                <x-country-options placeholder="Select a country" :selected="old('country')" />
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">This rate applies to</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="shippingRateScopeCountry" value="country" checked>
                                <label class="form-check-label" for="shippingRateScopeCountry">The whole country (its default rate)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="shippingRateScopeCity" value="city">
                                <label class="form-check-label" for="shippingRateScopeCity">One city only</label>
                            </div>
                        </div>
                        <div class="mb-3 d-none" id="shippingRateCityWrap">
                            <label class="form-label" for="shippingRateCity">City</label>
                            <select name="city" id="shippingRateCity" class="form-select">
                                <option value="">Select a city</option>
                            </select>
                            <input type="text" id="shippingRateCityOther" class="form-control mt-2" autocomplete="off" placeholder="Type the city's name" style="display:none;">
                            <div class="form-text">Every city of the chosen country is listed. For a place that isn't listed, choose "Other" at the bottom and type its name.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="shippingRateAmount">Delivery charge (PKR)</label>
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
<x-location-picker-js />
<script>
const shippingRateStoreUrl = "{{ route('admin.shipping-rates.store') }}";
const shippingRateModalEl  = document.getElementById('shippingRateModal');
const cityWrap  = document.getElementById('shippingRateCityWrap');
const cityInput = document.getElementById('shippingRateCity');
let shippingRateModal;

// Choosing a country loads that country's cities into the city field's suggestions
const rateLocation = LocationPicker.bind(
    document.getElementById('shippingRateCountry'),
    cityInput,
    document.getElementById('shippingRateCityOther'),
);

/* A city is only asked for when the rate is for one city; a disabled field isn't sent, so the rate is country-wide. */
function setScope(scope) {
    const forCity = scope === 'city';
    document.getElementById('shippingRateScopeCity').checked    = forCity;
    document.getElementById('shippingRateScopeCountry').checked = !forCity;
    cityWrap.classList.toggle('d-none', !forCity);
    cityInput.disabled = !forCity;
    cityInput.required = forCity;
}

document.querySelectorAll('input[name="scope"]').forEach(radio => radio.addEventListener('change', () => setScope(radio.value)));

/* Fill the form: from a saved rate (edit), a rejected attempt, or empty (add). */
function fillShippingRateForm(values, editingId) {
    const form = document.getElementById('shippingRateForm');

    document.getElementById('shippingRateModalTitle').textContent = editingId ? 'Edit Shipping Rate' : 'Add Shipping Rate';
    document.getElementById('shippingRateFormMethod').value = editingId ? 'PUT' : 'POST';
    document.getElementById('shippingRateEditingId').value  = editingId || '';
    form.action = editingId ? shippingRateStoreUrl + '/' + editingId : shippingRateStoreUrl;

    setScope(values.city ? 'city' : 'country');
    rateLocation.setLocation(values.country, values.city);
    document.getElementById('shippingRateAmount').value     = values.amount ?? '';
    document.getElementById('shippingRateIsActive').checked = values.is_active ?? true;
}

function openShippingRateModal(rate) {
    fillShippingRateForm(rate || {}, rate ? rate.id : null);
    shippingRateModal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    shippingRateModal = new bootstrap.Modal(shippingRateModalEl);

    @if ($errors->any())
        // Save was rejected: reopen the form as the admin left it, still editing the same rate
        fillShippingRateForm({
            country:   @json(old('country')),
            city:      @json(old('city')),
            amount:    @json(old('amount')),
            is_active: @json((bool) old('is_active')),
        }, @json(old('editing_id')));
        shippingRateModal.show();
    @else
        setScope('country');
    @endif
});
</script>
@endsection
