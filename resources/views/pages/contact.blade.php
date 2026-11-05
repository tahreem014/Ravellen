@extends('layouts.landing-page-layout')
@section('title', 'Contact Us')

@section('css')
<style>
    .contact-wrapper { background: #fdf2f8; min-height: 100vh; }

    /* ── Info Cards ── */
    .contact-info-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.07);
        transition: box-shadow .3s;
        background: #fff;
    }
    .contact-info-card:hover { box-shadow: 0 10px 28px rgba(0,0,0,.11); }
    .contact-icon-wrap {
        width: 2.5rem; height: 2.5rem; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }

    /* ── Form Card ── */
    .contact-form-card {
        border: 1px solid #fce7f3;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        background: #fff;
    }
    .contact-form-card .form-control,
    .contact-form-card textarea {
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        font-size: .9rem;
        padding: .55rem .85rem;
    }
    .contact-form-card .form-control:focus,
    .contact-form-card textarea:focus {
        border-color: #ec4899;
        box-shadow: 0 0 0 3px rgba(236,72,153,.12);
        outline: none;
    }
    .contact-form-card label { font-size: .85rem; font-weight: 500; color: #374151; margin-bottom: .35rem; }

    /* ── FAQ ── */
    .faq-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .75rem;
        overflow: hidden;
        margin-bottom: .75rem;
    }
    .faq-btn {
        width: 100%; text-align: left;
        background: none; border: 0;
        padding: 1rem 1.25rem;
        font-weight: 500; font-size: .95rem; color: #1f2937;
        display: flex; justify-content: space-between; align-items: center;
        cursor: pointer;
    }
    .faq-btn .faq-chevron { font-size: 1rem; color: #6b7280; transition: transform .3s ease; display: inline-block; }
    .faq-body { padding: 0 1.25rem 1rem; font-size: .875rem; color: #6b7280; }

    /* fade-up */
    .fade-up { opacity: 1; transform: translateY(0); }
</style>
@endsection

@section('content')
@php
    // The support email and phone saved in Admin → Settings
    $supportEmail = \App\Models\Setting::get('support_email');
    $supportPhone = \App\Models\Setting::get('support_phone');
@endphp
<div class="contact-wrapper py-5">
    <div class="container px-4" style="max-width:72rem;">

        {{-- ── Header ── --}}
        <div class="text-center mb-5 fade-up">
            <h1 class="fw-bold mb-2"
                style="font-size:clamp(2rem,5vw,3rem);text-black;">
                Get in Touch
            </h1>
            <p class="text-muted mx-auto" style="max-width:36rem;">
                Have a question about your order, mystery scoops, or just want to say hi? We'd love to hear from you!
            </p>
        </div>

        {{-- ── Info + Form ── --}}
        <div class="row g-4 mb-5">

            {{-- Info Cards --}}
            <div class="col-lg-4 fade-up">
                <div class="d-flex flex-column gap-3">

                    <div class="contact-info-card p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="contact-icon-wrap" style="background:#fce7f3;color:#ec4899;">
                                <i class="ri-mail-line"></i>
                            </div>
                            <span class="fw-semibold">Email Us</span>
                        </div>
                        <p class="text-muted small mb-1">For general inquiries and support:</p>
                        <a href="mailto:{{ $supportEmail }}" class="fw-medium text-decoration-none" style="color:#ec4899;overflow-wrap:anywhere;">{{ $supportEmail }}</a>
                    </div>

                    <div class="contact-info-card p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="contact-icon-wrap" style="background:#ede9fe;color:#9333ea;">
                                <i class="ri-phone-line"></i>
                            </div>
                            <span class="fw-semibold">Call Us</span>
                        </div>
                        <p class="text-muted small mb-1">Mon-Fri from 9am to 6pm:</p>
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $supportPhone) }}" class="fw-medium text-decoration-none" style="color:#9333ea;">{{ $supportPhone }}</a>
                    </div>

                </div>
            </div>

            {{-- Contact Form --}}
            <div class="col-lg-8 fade-up">
                <div class="contact-form-card p-4 p-md-5">
                    <h5 class="fw-bold mb-1">Send us a message</h5>
                    <p class="text-muted small mb-4">Fill out the form below and we'll respond within 24 hours.</p>

                    <form method="POST" action="{{ route('contact.send') }}">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="contact-name">Your Name</label>
                                <input type="text" id="contact-name" name="name" class="form-control" placeholder="Your name" value="{{ old('name') }}" maxlength="100" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contact-email">Email</label>
                                <input type="email" id="contact-email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" maxlength="255" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="contact-message">Message</label>
                            <textarea id="contact-message" name="message" class="form-control" rows="5" placeholder="Tell us more about your inquiry..." minlength="10" maxlength="3000" required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn text-white fw-semibold px-4 py-2"
                                style="background:#BE185D;border-radius:.5rem;">
                            <i class="ri-send-plane-line me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── FAQ ── --}}
        <div class="fade-up" style="max-width:44rem;margin:0 auto;">
            <div class="text-center mb-4">
                <h2 class="fw-bold d-flex align-items-center justify-content-center gap-2">
                    <i class="ri-question-line" style="color:#9333ea;"></i> Frequently Asked Questions
                </h2>
            </div>

            @php
                $faqs = [
                    ['q'=>'Where does Ravellen deliver?',
                     'a'=>'We currently deliver within Pakistan only. International shipping may become available in the future. Stay tuned for updates!'],
                    ['q'=>'What are mystery scoops and mystery balls?',
                     'a'=>'Mystery scoops contain a surprise selection of items, while each mystery ball contains one surprise item. We recommend watching our Instagram videos to understand how our mystery products work.'],
                    ['q'=>'How can I understand how mystery scoops and balls work?',
                     'a'=>'We recommend watching our Instagram videos and product demonstrations before ordering. They show how our mystery products are prepared and selected.'],
                    ['q'=>'Can I request specific items or preferences?',
                     'a'=>'Yes! You can mention your preferred colours, characters, themes, or items in the preference textbox while ordering. We’ll try our best to accommodate your requests, but specific items cannot be guaranteed.'],
                    ['q'=>'Can I request no duplicate items?',
                     'a'=>'Yes! Simply mention this preference before placing your order. We’ll try our best to avoid duplicates or replace them with other available items, but this cannot always be guaranteed.'],
                    ['q'=>'How long does delivery take?',
                     'a'=>'Orders generally require 1–3 business days for processing, followed by an estimated 5–7 business days for delivery. Delivery times may vary depending on your location and courier service.'],
                    ['q'=>'Can I return or exchange my order?',
                     'a'=>'We do not accept returns for change of mind or dissatisfaction with mystery items. Damaged, incorrect, or missing items may be eligible for assistance according to our Shipping & Returns Policy. Please review it before ordering.'],
                    ['q'=>'Do you accept pre-orders?',
                     'a'=>'Yes! Selected products may be available for pre-order. A 40% advance payment is required to secure your slot. The remaining 60% must be paid once the stock arrives, before your order is dispatched.'],
                ];
            @endphp

            @foreach($faqs as $i => $faq)
                <div class="faq-item">
                    <button class="faq-btn" type="button" onclick="toggleFaq(this)">
                        {{ $faq['q'] }}
                        <i class="ri-arrow-down-s-line faq-chevron"></i>
                    </button>
                    <div class="faq-body" style="{{ $i === 0 ? '' : 'display:none;' }}">{{ $faq['a'] }}</div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection

@section('script')
<script>
    function toggleFaq(btn) {
    const body = btn.nextElementSibling;
    const isOpen = body.style.display !== 'none';

    // Close all FAQs first (optional: remove if you want multiple open)
    document.querySelectorAll('.faq-body').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.faq-btn').forEach(el => {
        el.setAttribute('aria-expanded', 'false');
        el.querySelector('.faq-chevron').style.transform = 'rotate(0deg)';
    });

    // Toggle current
    if (!isOpen) {
        body.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.querySelector('.faq-chevron').style.transform = 'rotate(180deg)';
    }
}

    document.addEventListener('DOMContentLoaded', function () {
        // Set first FAQ chevron as open
        const firstBtn = document.querySelector('.faq-btn');
        if (firstBtn) {
            firstBtn.querySelector('.faq-chevron').style.transform = 'rotate(180deg)';
            firstBtn.setAttribute('aria-expanded', 'true');
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    });
</script>
@endsection
