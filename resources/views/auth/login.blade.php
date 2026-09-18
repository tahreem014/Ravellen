@extends('layouts.master-without-nav')
@section('title')
@lang('translation.signin')
@endsection

@section('content')
<style>
    body,
    html { background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important; }

    .rv-login-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem;
        font-family: 'Inter', sans-serif;
    }

    .rv-login-card {
        background: #ffffff !important;
        border-radius: .75rem !important;
        box-shadow: 0 8px 48px rgba(124,58,237,.10) !important;
        padding: 3rem 2.5rem !important;
        width: 100% !important;
        max-width: 32rem !important;
        border: none !important;
    }

    .rv-login-card .rv-input {
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

    .rv-login-card .rv-input:focus {
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

    .rv-btn-signin {
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
        position: relative !important;
        overflow: hidden !important;
    }
    .rv-btn-signin:hover { opacity: .88 !important; }
    .rv-btn-signin:disabled { opacity: .7 !important; cursor: not-allowed !important; }

    /* Loading bar that runs across the bottom of the button */
    .rv-btn-signin .btn-loader {
        display: none;
        width: 1.1rem; height: 1.1rem;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    .rv-btn-signin.loading .btn-loader { display: inline-block; }
    .rv-btn-signin.loading .btn-text   { opacity: .85; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Progress bar under button */
    .signin-progress {
        height: 3px;
        background: linear-gradient(to right, #f472b6, #a855f7);
        border-radius: 0 0 .5rem .5rem;
        width: 0; transition: width .8s ease;
        display: none;
    }
    .signin-progress.active { display: block; }

    .rv-btn-google {
        border: 1.5px solid #e5e7eb !important;
        text-decoration: none !important;
        border-radius: .75rem !important;
        background: #fff !important;
        color: #374151 !important;
        font-weight: 500 !important;
        font-size: .9rem !important;
        padding: .75rem !important;
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: .6rem !important;
        cursor: pointer !important;
        transition: background .2s !important;
        margin-top: .75rem !important;
    }
    .rv-btn-google:hover { background: #f9fafb !important; }

    .rv-divider {
        display: flex !important;
        align-items: center !important;
        gap: .75rem !important;
        color: #9ca3af !important;
        font-size: .8rem !important;
        margin: 1.25rem 0 !important;
    }
    .rv-divider::before,
    .rv-divider::after {
        content: '' !important;
        flex: 1 !important;
        height: 1px !important;
        background: #e5e7eb !important;
    }

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

<div class="rv-login-page">
    <div class="rv-login-card">

        {{-- Logo + heading --}}
        <div style="text-align:center;margin-bottom:2rem;">
            <a href="{{ url('/') }}" class="rv-logo text-decoration-none">
            <img src="{{ asset('images/ravellen-logo.png') }}" alt="Ravellen" height="60" style="display:block;margin:0 auto 1.25rem;">
            </a>
            <h4 style="font-weight:800;font-size:1.75rem;margin-bottom:.5rem;background:linear-gradient(to right,#6517AB,#9A1A8B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Welcome Back
            </h4>
            <p style="color:#6b7280;font-size:.875rem;margin:0;">Sign in to continue shopping</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            {{-- Email --}}
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-email">Email</label>
                <input type="email"
                placeholder="abc@gmail.com"
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
                {{-- <div class="d-flex justify-content-between align-items-center" style="margin-bottom:.45rem;"> --}}
                    <label class="rv-label" for="rv-password" style="margin-bottom:0;">Password</label>
                {{-- </div> --}}
                <div style="position:relative;">
                    <input type="password"
                    placeholder="password"
                    class="rv-input password-input @error('password') is-invalid @enderror"
                    id="rv-password" name="password"
                    autocomplete="current-password"
                    style="padding-right:2.75rem !important;">
                    <button type="button" class="rv-eye-btn password-addon" id="password-addon">
                        <i class="ri-eye-fill" style="font-size:1.1rem;"></i>
                    </button>
                </div>
                @error('password')
                <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>
            <div style="text-align:right;margin-bottom:1.5rem;">
                <a href="{{ route('password.request') }}" style="font-size:.8rem;color:#9333ea;font-weight:600;text-decoration:none;">Forgot password?</a>
            </div>

            {{-- Sign In button --}}
            <button type="submit" class="rv-btn-signin" id="signin-btn">
                <span class="btn-loader"></span>
                <span class="btn-text">Sign In</span>
            </button>
            <div class="signin-progress" id="signin-progress"></div>

            {{-- Divider --}}
            <div class="rv-divider">or</div>

            {{-- Google --}}
            <a href="{{ route('google.login') }}" class="rv-btn-google d-flex align-items-center justify-content-center gap-2">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Continue with Google
            </a>

        </form>

        {{-- Register --}}
        <p style="text-align:center;color:#6b7280;font-size:.9rem;margin-top:1.5rem;margin-bottom:0;">
            Don't have an account?
            <a href="{{ route('register') }}"
               style="font-weight:700;color:#9333ea;text-decoration:none;">Register here</a>
        </p>

    </div>
</div>

@endsection
@section('script')
<script>
// ── Password toggle ──────────────────────────────────────────────────────
document.getElementById('password-addon').addEventListener('click', function () {
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

// ── Sign In loading bar ──────────────────────────────────────────────────
document.querySelector('form').addEventListener('submit', function () {
    const btn      = document.getElementById('signin-btn');
    const progress = document.getElementById('signin-progress');

    btn.classList.add('loading');
    btn.disabled = true;

    progress.classList.add('active');
    // Animate progress bar to 90% then let page navigate
    setTimeout(() => { progress.style.width = '90%'; }, 50);
});
</script>
@endsection
