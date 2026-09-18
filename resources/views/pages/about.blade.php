@extends('layouts.landing-page-layout')

@section('css')
<style>
    .about-wrapper { background: #fff; min-height: 100vh; }

    /* ── Hero ── */
    .about-hero {
        background: linear-gradient(135deg, #F88BC4 0%, #FB7185 100%);
        padding: 5rem 0 4rem;
        color: #fff;
    }
    .about-hero h1 {
        font-size: clamp(2.2rem, 5vw, 3.5rem);
        font-weight: 800;
        color: #fde68a;
        margin-bottom: 1rem;
    }
    .about-hero p { color: rgba(255,255,255,.92); font-size: 1.05rem; max-width: 36rem; }

    /* ── Story ── */
    .story-img-wrap {
        border-radius: 1.25rem;
        overflow: hidden;
        border: 4px solid #fce7f3;
        transform: rotate(-2deg);
        transition: transform .5s;
        box-shadow: 0 20px 40px rgba(0,0,0,.12);
    }
    .story-img-wrap:hover { transform: rotate(0deg); }
    .story-img-wrap img { width: 100%; height: 26rem; object-fit: cover; display: block; }

    .since-badge {
        display: inline-block;
        background: #fce7f3; color: #ec4899;
        font-size: .8rem; font-weight: 700;
        padding: .25rem .75rem; border-radius: 2rem;
        margin-bottom: .75rem;
    }

    .value-icon-wrap {
        width: 3rem; height: 3rem; border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }

    /* ── Stats ── */
    .stats-section { background: #fdf2f8; }
    .stat-number { font-size: 2.5rem; font-weight: 800; }

    /* ── Values ── */
    .value-card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.07);
        transition: box-shadow .3s;
        text-align: center;
    }
    .value-card:hover { box-shadow: 0 12px 32px rgba(0,0,0,.12); }
    .value-icon-circle {
        width: 4rem; height: 4rem; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; margin: 0 auto 1rem;
    }

    /* ── CTA ── */
    .about-cta { background: #9333ea; color: #fff; }

    /* fade-up */
    .fade-up { opacity: 1; transform: translateY(24px); transition: opacity .6s ease, transform .6s ease; }
    .fade-up.visible { opacity: 1; transform: translateY(0); }
</style>
@endsection

@section('content')
<div class="about-wrapper">

    {{-- ── Hero ── --}}
    <section class="about-hero">
        <div class="container px-4">
            <h1>Unboxing Happiness</h1>
            <p>We are Ravellen - Pakistan's premier destination for Mystery Scoops, K-Pop Merch, and curated happiness delivered to your doorstep.</p>
        </div>
    </section>

    {{-- ── Our Story ── --}}
    <section class="py-5 my-3">
        <div class="container px-4">
            <div class="row align-items-center g-5">

                <div class="col-md-5 fade-up">
                    <div class="story-img-wrap">
                        <img src="https://images.unsplash.com/photo-1593522420322-18aefe34b480?w=800&q=80" alt="Mystery Gift Box">
                    </div>
                </div>

                <div class="col-md-7 fade-up">
                    <span class="since-badge">Since 2023</span>
                    <h2 class="fw-bold mb-3" style="font-size:1.9rem;">More Than Just a Store</h2>
                    <p class="text-muted mb-3">Ravellen began with a simple idea: the thrill of the unknown. What started as a small passion project for mystery scoops has grown into a comprehensive community for collectors and fans.</p>
                    <p class="text-muted mb-4">We understand the excitement of waiting for a package, the joy of unboxing, and the love for K-Pop culture. That's why every single scoop, box, and ball is packed with love, care, and a touch of magic.</p>

                    <div class="row g-3">
                        <div class="col-6 d-flex align-items-center gap-2">
                            <div class="value-icon-wrap" style="background:#f3e8ff;color:#9333ea;">
                                <i class="ri-heart-line"></i>
                            </div>
                            <span class="fw-medium text-dark">Made with Love</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <div class="value-icon-wrap" style="background:#fce7f3;color:#ec4899;">
                                <i class="ri-star-line"></i>
                            </div>
                            <span class="fw-medium text-dark">Premium Quality</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <div class="value-icon-wrap" style="background:#dbeafe;color:#3b82f6;">
                                <i class="ri-shield-check-line"></i>
                            </div>
                            <span class="fw-medium text-dark">Secure Shopping</span>
                        </div>
                        <div class="col-6 d-flex align-items-center gap-2">
                            <div class="value-icon-wrap" style="background:#fef9c3;color:#ca8a04;">
                                <i class="ri-gift-line"></i>
                            </div>
                            <span class="fw-medium text-dark">Surprise Gifts</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ── Stats ── --}}
    <section class="stats-section py-5 fade-up">
        <div class="container px-4">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-number" style="color:#ec4899;">5k+</div>
                    <div class="text-muted">Happy Customers</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number" style="color:#9333ea;">10k+</div>
                    <div class="text-muted">Scoops Sold</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number" style="color:#ec4899;">50+</div>
                    <div class="text-muted">Merch Categories</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-number" style="color:#9333ea;">4.9</div>
                    <div class="text-muted">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Core Values ── --}}
    <section class="py-5 my-3">
        <div class="container px-4">
            <div class="text-center mb-5 fade-up">
                <h2 class="fw-bold mb-2">Our Core Values</h2>
                <p class="text-muted">We believe in transparency, quality, and customer satisfaction above all else.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 fade-up">
                    <div class="value-card card p-4 h-100" style="background:linear-gradient(to bottom right,#fff,#fdf2f8);">
                        <div class="value-icon-circle" style="background:#fce7f3;color:#ec4899;">
                            <i class="ri-sparkling-2-line"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Magical Experience</h5>
                        <p class="text-muted small mb-0">Every interaction with Ravellen should feel like magic. From browsing to unboxing, we strive for delight.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up">
                    <div class="value-card card p-4 h-100" style="background:linear-gradient(to bottom right,#fff,#f5f3ff);">
                        <div class="value-icon-circle" style="background:#ede9fe;color:#9333ea;">
                            <i class="ri-group-line"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Community First</h5>
                        <p class="text-muted small mb-0">We are built by fans, for fans. We listen to our community and constantly evolve based on your feedback.</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up">
                    <div class="value-card card p-4 h-100" style="background:linear-gradient(to bottom right,#fff,#fdf2f8);">
                        <div class="value-icon-circle" style="background:#fce7f3;color:#ec4899;">
                            <i class="ri-shield-check-line"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Trust & Quality</h5>
                        <p class="text-muted small mb-0">Authenticity is our promise. Whether it's official BTS merch or our mystery items, we guarantee quality.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    });
</script>
@endsection
