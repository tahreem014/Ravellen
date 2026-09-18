@extends('layouts.master-without-nav')

@section('content')
<style>
    body, html { background: linear-gradient(135deg,#fde8ee 0%,#f3eafc 50%,#eaf3fc 100%) !important; }
    .rv-fp-page {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 2rem 1rem;
        font-family: 'Inter', sans-serif;
    }
    .rv-fp-card {
        background: #fff; border-radius: .75rem;
        box-shadow: 0 8px 48px rgba(124,58,237,.10);
        padding: 3rem 2.5rem; width: 100%; max-width: 30rem;
    }
    .rv-input {
        background: #ebebeb; border: none; border-radius: .5rem;
        padding: .65rem 1.1rem; font-size: .95rem; color: #374151;
        width: 100%; outline: none; display: block; box-sizing: border-box;
    }
    .rv-input:focus { background: #e2e2e2; }
    .rv-input.is-invalid { outline: 2px solid #ef4444; }
    .rv-label { font-size: .875rem; font-weight: 700; color: #111827; margin-bottom: .45rem; display: block; }
    .rv-btn {
        background: linear-gradient(to right,#f472b6,#ec4899);
        border: none; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: 1rem; padding: .65rem;
        width: 100%; cursor: pointer; transition: opacity .2s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .rv-btn:hover { opacity: .88; }
    .rv-btn:disabled { opacity: .7; cursor: not-allowed; }
    .rv-btn .btn-loader {
        display: none; width: 1.1rem; height: 1.1rem;
        border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
        border-radius: 50%; animation: spin .7s linear infinite;
    }
    .rv-btn.loading .btn-loader { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="rv-fp-page">
    <div class="rv-fp-card">

        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="rv-logo text-decoration-none">
            <img src="{{ asset('images/ravellen-logo.png') }}" alt="Ravellen" height="60" style="display:block;margin:0 auto 1.25rem;">
            </a>
            <h4 style="font-weight:800;font-size:1.5rem;margin-bottom:.3rem;background:linear-gradient(to right,#6517AB,#9A1A8B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Forgot Password?
            </h4>
            <p style="color:#6b7280;font-size:.85rem;margin:0;">Enter your email and we'll send you a reset link</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" id="fp-form">
            @csrf
            <div style="margin-top:1.5rem;margin-bottom:1.5rem;">
                <label class="rv-label" for="fp-email">Email Address</label>
                <input type="email" class="rv-input @error('email') is-invalid @enderror"
                       id="fp-email" name="email"
                       value="{{ old('email') }}"
                       placeholder="john@example.com"
                       autocomplete="email">
                @error('email')
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="rv-btn" id="fp-btn">
                <span class="btn-loader"></span>
                <span>Send Reset Link</span>
            </button>
        </form>

        <p style="text-align:center;color:#6b7280;font-size:.875rem;margin-top:1.5rem;margin-bottom:0;">
            Remember your password?
            <a href="{{ route('login') }}" style="font-weight:700;color:#9333ea;text-decoration:none;">Sign In</a>
        </p>

    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('fp-form').addEventListener('submit', function () {
    const btn = document.getElementById('fp-btn');
    btn.classList.add('loading');
    btn.disabled = true;
});
</script>
@endsection
