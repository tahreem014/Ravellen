{{--
    Shown on the login and register pages to a guest who pressed "Place Order": their details are
    waiting in the session (App\Support\CheckoutResume) and checkout resumes once they're in.
--}}
@if(\App\Support\CheckoutResume::pending())
    <div style="display:flex;gap:.6rem;align-items:flex-start;background:#faf5ff;border:1px solid #e9d5ff;border-radius:.6rem;padding:.8rem 1rem;margin-bottom:1.5rem;font-size:.85rem;line-height:1.45;color:#6b21a8;">
        <i class="ri-shield-check-line" style="font-size:1.15rem;flex-shrink:0;"></i>
        <span><strong>One last step.</strong> Log in or create an account to finish your order. Your cart and shipping details are saved for {{ \App\Support\CheckoutResume::MINUTES }} minutes.</span>
    </div>
@endif
