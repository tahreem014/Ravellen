<footer style="background: linear-gradient(to right, #BE185D 0%, #F472B6 50%, #EC4899 100%); color: #fff; margin-top: auto;">
    <div class="container px-4 py-5">
        <div class="row align-items-start">

            {{-- Brand --}}
            <div class="col-12 col-md-4 pe-md-5 mb-4 mb-md-0">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-white rounded-circle p-4 d-flex align-items-center justify-content-center"
                         style="width:2.5rem;height:2.5rem;">
                            <img src="{{ asset('images/ravellen-logo.png') }}" alt="Ravellen Logo" height="25">
                    </div>
                    <span class="fw-bold fs-5">Ravellen</span>
                </div>
                <p style="color:rgba(255,255,255,.8);font-size:.875rem;">
                    Your destination for mystery scoops, BTS merch, and curated gift boxes. Every order is a surprise!
                </p>
            </div>

            {{-- Links Group --}}
            <div class="col-12 col-md-8">
                <div class="row g-4">

                    {{-- Quick Links --}}
                    <div class="col-6 col-md-4">
                        <h6 class="fw-bold fs-6 mb-3 text-white">Quick Links</h6>
                        <ul class="list-unstyled mb-0" style="color:rgba(255,255,255,.8);font-size:.875rem;">
                            <li class="mb-2"><a href="{{ url('/about') }}"          class="text-white-50 text-decoration-none footer-link">About Us</a></li>
                            <li class="mb-2"><a href="{{ url('/contact') }}"        class="text-white-50 text-decoration-none footer-link">Contact Us</a></li>
                            <li class="mb-2"><a href="{{ url('/shop') }}"           class="text-white-50 text-decoration-none footer-link">Shop</a></li>
                            <li class="mb-2"><a href="{{ route('profile', ['tab' => 'orders']) }}" class="text-white-50 text-decoration-none footer-link">Track Order</a></li>
                            <li class="mb-2"><a href="{{ route('contact') }}#faq"   class="text-white-50 text-decoration-none footer-link">FAQs</a></li>
                        </ul>
                    </div>

                    {{-- Policies --}}
                    <div class="col-6 col-md-4">
                        <h6 class="fw-bold fs-6 mb-3 text-white">Policies</h6>
                        <ul class="list-unstyled mb-0" style="color:rgba(255,255,255,.8);font-size:.875rem;">
                            <li class="mb-2"><a href="{{ route('privacy') }}"  class="text-white-50 text-decoration-none footer-link">Privacy Policy</a></li>
                            <li class="mb-2"><a href="{{ route('shipping') }}" class="text-white-50 text-decoration-none footer-link">Shipping &amp; Returns</a></li>
                            <li class="mb-2"><a href="{{ route('terms') }}"    class="text-white-50 text-decoration-none footer-link">Terms of Service</a></li>
                        </ul>
                    </div>

                    {{-- Contact & Social --}}
                    <div class="col-12 col-md-4">
                        <h6 class="fw-bold fs-6 mb-3 text-white">Contact Us</h6>
                        @php
                            // The support email and phone saved in Admin → Settings
                            $footerEmail = \App\Models\Setting::get('support_email');
                            $footerPhone = \App\Models\Setting::get('support_phone');
                        @endphp
                        <ul class="list-unstyled mb-0" style="color:rgba(255,255,255,.8);font-size:.875rem;">
                            @if($footerEmail)
                                <li class="mb-2 d-flex align-items-center gap-2">
                                    <i class="ri-mail-line"></i>
                                    <a href="mailto:{{ $footerEmail }}" class="text-white-50 text-decoration-none footer-link">{{ $footerEmail }}</a>
                                </li>
                            @endif
                            @if($footerPhone)
                                <li class="d-flex align-items-center gap-2">
                                    <i class="ri-phone-line"></i>
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $footerPhone) }}" class="text-white-50 text-decoration-none footer-link">{{ $footerPhone }}</a>
                                </li>
                            @endif
                        </ul>

                        {{-- Social Media --}}
                        <div class="d-flex gap-2 mt-4">
                            <a href="https://www.instagram.com/ravellen_nm" target="_blank" rel="noopener noreferrer" aria-label="Ravellen on Instagram"
                               class="footer-social-btn d-flex align-items-center justify-content-center rounded-circle text-decoration-none"
                               style="width:2.25rem;height:2.25rem;background:rgba(255,255,255,.2);">
                                <i class="ri-instagram-line"></i>
                            </a>
                            <a href="https://www.tiktok.com/@ravellen_nm" target="_blank" rel="noopener noreferrer" aria-label="Ravellen on TikTok"
                               class="footer-social-btn d-flex align-items-center justify-content-center rounded-circle text-decoration-none"
                               style="width:2.25rem;height:2.25rem;background:rgba(255,255,255,.2);">
                                <i class="ri-tiktok-line"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        {{-- Bottom Bar --}}
        <div class="mt-4 pt-4 text-center" style="border-top:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.6);font-size:.875rem;">
            <p class="mb-0">© {{ date('Y') }} Ravellen. All rights reserved. | Made with love for mystery lovers and ARMY 💜</p>
        </div>
    </div>
</footer>

<style>
    .footer-link { overflow-wrap: anywhere; }   /* a long email or number wraps instead of running off the screen */
    .footer-link:hover { color: #fff !important; }
    .footer-social-btn:hover { background: rgba(255,255,255,.35) !important; }
    .footer-social-btn { color: #fff; transition: background .2s; }
    .footer-social-btn, .footer-social-btn:hover, .footer-social-btn:focus { text-decoration: none; color: #fff; }   /* no underline on the icons, even on hover */
</style>
