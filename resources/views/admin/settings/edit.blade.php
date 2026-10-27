@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <div class="admin-card card" style="max-width:640px;">
        <div class="card-body">
            <h5 class="mb-3">Store Settings</h5>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Support Email</label>
                    <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Support Phone</label>
                    <input type="text" name="support_phone" value="{{ old('support_phone', $settings['support_phone']) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pre-order Advance Rate (0–1)</label>
                    <input type="number" step="0.01" min="0" max="1" name="preorder_advance_rate" value="{{ old('preorder_advance_rate', $settings['preorder_advance_rate']) }}" class="form-control" required>
                    <div class="form-text">e.g. 0.4 = 40% advance, 60% collected once the item is back in stock.</div>
                </div>

                <div class="alert alert-info small">
                    This value is stored for reference and future use. The live checkout still uses the advance-rate constant in code (<code>OrderService</code>) — updating it here does not change checkout behavior yet.
                </div>

                <button type="submit" class="btn btn-admin-primary">Save Settings</button>
            </form>
        </div>
    </div>

    {{-- On a phone the header has no Log Out, so it lives here (the header keeps it from tablet width up) --}}
    <div class="admin-card card mt-4 d-md-none">
        <div class="card-body">
            <h5 class="mb-1">Account</h5>
            <p class="text-muted small mb-3">Signed in as {{ Auth::user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-admin-logout w-100">
                    <i class="ri-logout-box-r-line"></i> Log Out
                </button>
            </form>
        </div>
    </div>
@endsection
