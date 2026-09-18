@extends('layouts.landing-page-layout')

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
                        <a href="mailto:hello@ravellen.com" class="fw-medium text-decoration-none" style="color:#ec4899;">hello@ravellen.com</a>
                    </div>

                    <div class="contact-info-card p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="contact-icon-wrap" style="background:#ede9fe;color:#9333ea;">
                                <i class="ri-phone-line"></i>
                            </div>
                            <span class="fw-semibold">Call Us</span>
                        </div>
                        <p class="text-muted small mb-1">Mon-Fri from 9am to 6pm:</p>
                        <a href="tel:+923001234567" class="fw-medium text-decoration-none" style="color:#9333ea;">+92 300 1234567</a>
                    </div>

                </div>
            </div>

            {{-- Contact Form --}}
            <div class="col-lg-8 fade-up">
                <div class="contact-form-card p-4 p-md-5">
                    <h5 class="fw-bold mb-1">Send us a message</h5>
                    <p class="text-muted small mb-4">Fill out the form below and we'll respond within 24 hours.</p>

                    <form>
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label>Your Name</label>
                                <input type="text" class="form-control" placeholder="[name]" required>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control" placeholder="[email]" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label>Message</label>
                            <textarea class="form-control" rows="5" placeholder="Tell us more about your inquiry..." required></textarea>
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
                    ['q'=>'How long does shipping take?',
                     'a'=>'Standard shipping typically takes 3-5 business days within major cities in Pakistan. For remote areas, it might take 5-7 days.'],
                    ['q'=>'Can I return my Mystery Scoop?',
                     'a'=>'Due to the nature of mystery items, we generally do not accept returns on Mystery Scoops unless items arrive damaged. Please record an unboxing video as proof for any claims.'],
                    ['q'=>'Do you ship internationally?',
                     'a'=>'Currently, we only ship within Pakistan. We are working on expanding our reach to international customers soon!'],
                    ['q'=>'How do pre-orders work?',
                     'a'=>'For pre-order items, you need to pay a 40% advance to secure your order. The remaining amount is payable when the item arrives and is ready for shipping.'],
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
    const isOpen = body.style.display === 'block';

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
