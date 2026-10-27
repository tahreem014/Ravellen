@extends('layouts.master-without-nav')
@section('title')
@lang('translation.signup')
@endsection

@section('content')
<style>
    body,
    html { background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important; }

    .rv-register-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem;
        font-family: 'Inter', sans-serif;
    }

    .rv-register-card {
        background: #ffffff !important;
        border-radius: .75rem !important;
        box-shadow: 0 8px 48px rgba(124,58,237,.10) !important;
        padding: 3rem 2.5rem !important;
        width: 100% !important;
        max-width: 32rem !important;
        border: none !important;
    }

    .rv-register-card .rv-input {
        background: #ebebeb !important;
        border: none !important;
        border-radius: .5rem !important;
        padding: .6rem 1.1rem !important;
        font-size: .95rem !important;
        color: #374151 !important;
        width: 100% !important;
        box-shadow: none !important;
        outline: none !important;
        display: block !important;
        box-sizing: border-box !important;
    }

    .rv-register-card .rv-input:focus {
        background: #e2e2e2 !important;
        box-shadow: none !important;
        border: none !important;
        outline: none !important;
    }

    .rv-label {
        font-size: .875rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin-bottom: .45rem !important;
        display: block !important;
    }

    .rv-btn-register {
        background: linear-gradient(to right, #f472b6, #ec4899) !important;
        border: none !important;
        border-radius: .5rem !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
        padding: .5rem !important;
        width: 100% !important;
        cursor: pointer !important;
        transition: opacity .2s !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: .5rem !important;
    }
    .rv-btn-register:hover { opacity: .88 !important; }
    .rv-btn-register:disabled { opacity: .7 !important; cursor: not-allowed !important; }

    .rv-btn-register .btn-loader {
        display: none;
        width: 1.1rem; height: 1.1rem;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    .rv-btn-register.loading .btn-loader { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .register-progress {
        height: 3px;
        background: linear-gradient(to right, #f472b6, #a855f7);
        border-radius: 0 0 .5rem .5rem;
        width: 0; transition: width .8s ease; display: none;
    }
    .register-progress.active { display: block; }

    .rv-eye-btn {
        position: absolute !important;
        right: .85rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: none !important;
        border: none !important;
        color: #9ca3af !important;
        cursor: pointer !important;
        padding: 0 !important;
        line-height: 1 !important;
    }
</style>

<div class="rv-register-page">
    <div class="rv-register-card">

        {{-- Logo + heading --}}
        <div style="text-align:center; margin-bottom:2rem;">
            <a href="{{ url('/') }}" class="rv-logo text-decoration-none">
            <img src="{{ asset('images/ravellen-logo.png') }}" alt="Ravellen" height="56" class="mb-3">
            </a>
            <h4 style="
                font-weight:800;
                font-size:1.65rem;
                margin-bottom:.3rem;
                background: linear-gradient(to right, #6517AB, #9A1A8B);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            ">Create Account</h4>
            <p style="color:#6b7280;font-size:.875rem;margin:0;">Join Ravellen and start your mystery adventure</p>
        </div>

        <x-checkout-login-notice />

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Full Name --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-name">Full Name</label>
                <input type="text"
                       placeholder="John Doe"
                       class="rv-input @error('name') is-invalid @enderror"
                       id="rv-name" name="name"
                       value="{{ old('name') }}"
                       autocomplete="name">
                @error('name')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-email">Email</label>
                <input type="email"
                       placeholder="john@example.com"
                       class="rv-input @error('email') is-invalid @enderror"
                       id="rv-email" name="email"
                       value="{{ old('email') }}"
                       autocomplete="email">
                @error('email')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-password">Password</label>
                <div style="position:relative;">
                    <input type="password"
                           placeholder="Min. 8 characters"
                           class="rv-input password-input @error('password') is-invalid @enderror"
                           id="rv-password" name="password"
                           autocomplete="new-password"
                           style="padding-right:2.75rem !important;">
                    <button type="button" class="rv-eye-btn password-addon">
                        <i class="ri-eye-fill" style="font-size:1.1rem;"></i>
                    </button>
                </div>
                @error('password')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-password-confirm">Confirm Password</label>
                <div style="position:relative;">
                    <input type="password"
                           placeholder="Re-enter password"
                           class="rv-input @error('password_confirmation') is-invalid @enderror"
                           id="rv-password-confirm" name="password_confirmation"
                           autocomplete="new-password"
                           style="padding-right:2.75rem !important;">
                </div>
                @error('password_confirmation')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Phone Number --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-phone">Phone Number</label>
                <input type="tel"
                       placeholder="+923001234567"
                       class="rv-input @error('phone_number') is-invalid @enderror"
                       id="rv-phone" name="phone_number"
                       value="{{ old('phone_number') }}"
                       autocomplete="tel">
                @error('phone_number')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            {{-- Create Account button --}}
            <button type="submit" class="rv-btn-register" id="register-btn">
                <span class="btn-loader"></span>
                <span class="btn-text">Create Account</span>
            </button>
            <div class="register-progress" id="register-progress"></div>

        </form>

        {{-- Sign in link --}}
        <p style="text-align:center;color:#6b7280;font-size:.9rem;margin-top:1.5rem;margin-bottom:0;">
            Already have an account?
            <a href="{{ route('login') }}"
               style="font-weight:700;color:#9333ea;text-decoration:none;">Sign In here</a>
        </p>

    </div>
</div>

@endsection
@section('script')
<script>
// ── Password toggle ──────────────────────────────────────────────────────
document.querySelector('.password-addon').addEventListener('click', function () {
    const input = document.getElementById('rv-password');
    const icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('ri-eye-fill', 'ri-eye-off-fill');
    } else {
        input.type = 'password';
        icon.classList.replace('ri-eye-off-fill', 'ri-eye-fill');
    }
});

// ── Register loading bar ─────────────────────────────────────────────────
document.querySelector('form').addEventListener('submit', function () {
    const btn      = document.getElementById('register-btn');
    const progress = document.getElementById('register-progress');
    btn.classList.add('loading');
    btn.disabled = true;
    progress.classList.add('active');
    setTimeout(() => { progress.style.width = '90%'; }, 50);
});
</script>
@endsection
