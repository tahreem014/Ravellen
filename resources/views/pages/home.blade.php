@extends('layouts.landing-page-layout')
@section('title', 'Home')

@section('css')
<style>
    /* ── General ── */
    .home-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    /* ── Hero ── */
    .hero-section {
        position: relative;
        overflow: hidden;
        /* background: linear-gradient(135deg, #ec4899 0%, #a855f7 50%, #7c3aed 100%); */
        background: linear-gradient(135deg, #F88BC4 0%, #FB7185 100%);
        color: #fff;
        min-height: 90vh; 
    }
    .hero-blob-1 {
        position: absolute; top: -6rem; right: -6rem;
        width: 24rem; height: 24rem;
        background: rgba(255,255,255,.10);
        border-radius: 50%; filter: blur(60px);
        animation: blobFloat1 20s ease-in-out infinite;
    }
    .hero-blob-2 {
        position: absolute; bottom: -6rem; left: -6rem;
        width: 24rem; height: 24rem;
        background: rgba(249,168,212,.20);
        border-radius: 50%; filter: blur(60px);
        animation: blobFloat2 15s ease-in-out infinite;
    }
    @keyframes blobFloat1 { 0%,100%{transform:scale(1) rotate(0deg)} 50%{transform:scale(1.2) rotate(90deg)} }
    @keyframes blobFloat2 { 0%,100%{transform:scale(1) rotate(0deg)} 50%{transform:scale(1.3) rotate(-90deg)} }

    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,.20);
        border: 1px solid rgba(255,255,255,.30);
        color: #fff;
        border-radius: 6px;
        padding: .25rem .75rem;
        font-size: .8rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    .hero-title { font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 800; line-height: 1.1; }
    .hero-title .gradient-text {
        display: block;
        background: linear-gradient(to right, #fde68a, #fbcfe8);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* floating category grid card */
    .hero-grid-wrap {
        background: rgba(255,255,255,.10);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.20);
        border-radius: 1.5rem;
        padding: 2rem;
        animation: heroFloat 6s ease-in-out infinite;
    }
    @keyframes heroFloat { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-20px) rotate(5deg)} }

    .hero-cat-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1rem;
        cursor: pointer;
        transition: box-shadow .2s, transform .2s;
        text-decoration: none;
        display: block;
    }
    .hero-cat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.12); transform: translateY(-3px); }
    .hero-cat-icon {
        width: 2.5rem; height: 2.5rem;
        border-radius: .75rem;
        display: flex; align-items: center; justify-content: start; padding-inline-start: 8px;
        margin-bottom: .5rem;
        font-size: 1.1rem; color: #fff;
    }

    /* wave */
    .hero-wave { position: absolute; bottom: 0; left: 0; right: 0; line-height: 0; }

    /* ── Features ── */
    .feature-icon-wrap {
        width: 4rem; height: 4rem;
        background: #FBCFE8;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem; color: #EC4899;
    }
    .feature-icon-wrap.army {
        background: #EDE9FE;
        color: #7C3AED;
    }

    /* ── Category Cards ── */
    .cat-card {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,.10);
        transition: transform .3s, box-shadow .3s;
        cursor: pointer;
    }
    .cat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,.15); }
    .cat-card-img-wrap { position: relative; height: 16rem; overflow: hidden; }
    .cat-card-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .5s;
    }
    .cat-card:hover .cat-card-img-wrap img { transform: scale(1.1); }
    .cat-card-overlay {
        position: absolute; inset: 0;
        opacity: .6;
        transition: opacity .3s;
    }
    .cat-card:hover .cat-card-overlay { opacity: .5; }
    .cat-card-icon-wrap {
        position: absolute; top: 1rem; right: 1rem;
        width: 3rem; height: 3rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.2);
    }
    .cat-badge {
        position: absolute; top: 1rem; left: 1rem;
        background: #facc15; color: #713f12;
        font-size: .7rem; font-weight: 700;
        padding: .2rem .6rem; border-radius: .4rem;
    }
    .cat-explore-btn {
        border: 0; color: #fff; font-weight: 600;
        transition: opacity .2s;
    }
    .cat-explore-btn:hover { opacity: .85; color: #fff; }

    /* ── CTA ── */
    .cta-section {
        /* background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%); */
        background: #FE94CE;
        color: #fff;
    }
    .cta-pulse { animation: pulse 2s ease-in-out infinite; }
    @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }

    /* fade-up on scroll */
    .fade-up { opacity: 1; transform: translateY(30px); transition: opacity .6s ease, transform .6s ease; }
    .fade-up.visible { opacity: 1; transform: translateY(0); }
</style>
@endsection

@section('content')

<div class="home-wrapper">

    {{-- ═══════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════ --}}
    <section class="hero-section py-5 py-md-6 ">
        <div class="hero-blob-1"></div>
        <div class="hero-blob-2"></div>

        <div class="container position-relative" style="z-index:1;">
            <div class="row align-items-center gy-5">

                {{-- Left --}}
                <div class="col-lg-6">
                    <span class="hero-badge">✨ New Collections Available</span>

                    <h1 class="hero-title mb-4 text-white">
                        Discover Your
                        <span class="gradient-text">Mystery Surprise</span>
                    </h1>

                    <p class="fs-5 mb-4" style="color:rgba(255,255,255,.9);">
                        Curated mystery scoops, BTS merch, and themed gift boxes.
                        Every order is an adventure! 🎁
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ url('/shop') }}"
                           class="btn btn-light fw-semibold px-4 py-2"
                           style="color:#7c3aed;">
                            Shop Now <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                        <a href="{{ url('/bts-merch') }}"
                           class="btn btn-outline-light px-4 py-2">
                            BTS Collection 💜
                        </a>
                    </div>
                </div>

                {{-- Right floating grid --}}
                <div class="col-lg-6">
                    <div class="hero-grid-wrap">
                        <div class="row g-3">
                            @php
                                $heroCategories = [
                                    ['name'=>'Mystery Scoops','link'=>'mystery-scoops','icon'=>'ri-gift-line',      'bg'=>'linear-gradient(135deg,#f472b6,#fb7185)'],
                                    ['name'=>'Mystery Balls', 'link'=>'mystery-balls', 'icon'=>'ri-sparkling-line', 'bg'=>'linear-gradient(135deg,#f472b6,#fb7185)'],
                                    ['name'=>'BTS Merch',     'link'=>'bts-merch',     'icon'=>'ri-star-line',      'bg'=>'linear-gradient(135deg,#C084FC,#9333EA)'],
                                    ['name'=>'Gift Box',      'link'=>'gift-boxes',     'icon'=>'ri-shopping-bag-line','bg'=>'linear-gradient(135deg,#fb7185,#f472b6)'],
                                ];
                            @endphp

                            @foreach($heroCategories as $cat)
                                <div class="col-6">
                                    <a href="{{ url($cat['link']) }}" class="hero-cat-card">
                                        <div class="hero-cat-icon w-100" style="background:{{ $cat['bg'] }};">
                                            <i class="{{ $cat['icon'] }}"></i>
                                        </div>
                                        <p class="fw-semibold text-dark mb-0 small">{{ $cat['name'] }}</p>
                                    </a>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Wave --}}
        <div class="hero-wave">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="#fdf2f8"/>
            </svg>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         FEATURES
    ═══════════════════════════════════════════ --}}
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                @php
                    $features = [
                        ['icon'=>'ri-gift-line',       'title'=>'Mystery Items',   'desc'=>'Curated surprises in every order'],
                        ['icon'=>'ri-sparkling-line',  'title'=>'Premium Quality', 'desc'=>'High-quality products guaranteed'],
                        ['icon'=>'ri-shopping-bag-line','title'=>'Perfect Gifts',  'desc'=>'Ideal for any occasion'],
                        ['icon'=>'ri-star-line',        'title'=>'ARMY Approved',  'desc'=>'Exclusive BTS merchandise'],
                    ];
                @endphp

                @foreach($features as $f)
                    <div class="col-6 col-md-3 fade-up">
                        <div class="card border-0 rounded-4 shadow-lg h-100 text-center">
                            <div class="card-body p-4">
                                <div class="feature-icon-wrap {{ $f['title'] === 'ARMY Approved' ? 'army' : '' }}">
                                    <i class="{{ $f['icon'] }} fs-4"></i>
                                </div>
                                <h6 class="fw-semibold mb-1">{{ $f['title'] }}</h6>
                                <p class="text-muted small mb-0">{{ $f['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>

    
    {{-- ═══════════════════════════════════════════
         CATEGORIES
    ═══════════════════════════════════════════ --}}
    <section class="py-5">
        <div class="container">

            <div class="text-center mb-5 fade-up">
                <h2 class="fw-bold fs-1"
                    style="background:#7c3aed;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    Shop by Category
                </h2>
                <p class="text-muted fs-5">Discover our amazing collections</p>
            </div>

            @php
                // UI config keyed by exact DB category name
                $homeCatConfig = [
                    'Mystery Scoops' => [
                        'link'    => 'mystery-scoops',
                        'icon'    => 'ri-gift-line',
                        'overlay' => 'linear-gradient(to top,rgba(236,72,153,.7),rgba(251,113,133,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#f472b6,#fb7185)',
                        'btnBg'   => 'linear-gradient(135deg,#f472b6,#fb7185)',
                        'fallback'=> 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=400&q=80',
                    ],
                    'Mystery Balls' => [
                        'link'    => 'mystery-balls',
                        'icon'    => 'ri-sparkling-line',
                        'overlay' => 'linear-gradient(to top,rgba(168,85,247,.7),rgba(236,72,153,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#c084fc,#f472b6)',
                        'btnBg'   => 'linear-gradient(135deg,#f472b6,#fb7185)',
                        'fallback'=> 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=400&q=80',
                    ],
                    'BTS Merch' => [
                        'link'    => 'bts-merch',
                        'icon'    => 'ri-star-line',
                        'overlay' => 'linear-gradient(to top,rgba(124,58,237,.7),rgba(167,139,250,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#818cf8,#a78bfa)',
                        'btnBg'   => 'linear-gradient(135deg,#C084FC,#9333EA)',
                        'fallback'=> 'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=400&q=80',
                    ],
                    'Gift Boxes' => [
                        'link'    => 'gift-boxes',
                        'icon'    => 'ri-shopping-bag-line',
                        'overlay' => 'linear-gradient(to top,rgba(244,63,94,.7),rgba(251,113,133,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#fb7185,#f472b6)',
                        'btnBg'   => 'linear-gradient(135deg,#fb7185,#f472b6)',
                        'fallback'=> 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=400&q=80',
                    ],
                    'BTS Gift Boxes' => [
                        'link'    => 'bts-gift-boxes',
                        'icon'    => 'ri-gift-2-line',
                        'overlay' => 'linear-gradient(to top,rgba(124,58,237,.7),rgba(167,139,250,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#818cf8,#a78bfa)',
                        'btnBg'   => 'linear-gradient(135deg,#C084FC,#9333EA)',
                        'fallback'=> 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=400&q=80',
                    ],
                    'Ceramic Items' => [
                        'link'    => 'ceramic-items',
                        'icon'    => 'ri-cup-line',
                        'overlay' => 'linear-gradient(to top,rgba(139,94,60,.5),rgba(200,169,138,.4))',
                        'iconBg'  => 'linear-gradient(135deg,#C8A98A,#8B5E3C)',
                        'btnBg'   => 'linear-gradient(135deg,#C8A98A,#8B5E3C)',
                        'fallback'=> 'https://images.unsplash.com/photo-1515401857507-c897a8f64e42?w=400&q=80',
                    ],
                ];
            @endphp

            <div class="row g-4">
                @forelse($categories as $cat)
                @php
                    $cfg = $homeCatConfig[$cat->name] ?? null;

                    // First available image across all products/sub-products
                    // (a product's category relation also matches products that belong
                    // to one of its sub-categories, so only take direct ones here)
                    $allProds = $cat->products->whereNull('sub_category_id');
                    foreach ($cat->subCategories as $sub) {
                        $allProds = $allProds->concat($sub->products);
                    }
                    $firstImg  = isset($cfg) ? ($allProds->first(fn($p) => $p->images->isNotEmpty())?->images->first()?->image_url ?? $cfg['fallback']) : null;
                    $cheapest  = \App\Models\ProductVariant::cheapestOf($allProds->flatMap(fn($p) => $p->variants));
                    $pDesc     = $cat->subCategories->count() . ' sub-categories, ' . $allProds->count() . ' products';
                    $catLabel  = isset($cfg) ? ($cfg['label'] ?? $cat->name) : $cat->name;
                    $soon      = $cat->isComingSoon();   // nothing on sale yet: no product photo, price or count
                    if ($soon && isset($cfg)) { $firstImg = $cfg['fallback']; }
                @endphp
                @if($cfg)
                <div class="col-md-6 col-lg-4 fade-up" @unless($soon) data-live-category="{{ $cat->id }}" @endunless>
                    <a href="{{ url($cfg['link']) }}" class="text-decoration-none">
                        <div class="cat-card card">
                            <div class="cat-card-img-wrap">
                                <img src="{{ $firstImg }}" alt="{{ $catLabel }}">
                                @if($soon)
                                    <span style="position:absolute;top:.75rem;left:.75rem;z-index:2;background:#fff;color:#8B5E3C;font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .8rem;border-radius:2rem;box-shadow:0 2px 8px rgba(0,0,0,.15);">Coming Soon</span>
                                @endif
                                <div class="cat-card-overlay" style="background:{{ $cfg['overlay'] }};"></div>
                                <div class="cat-card-icon-wrap" style="background:{{ $cfg['iconBg'] }};">
                                    <i class="{{ $cfg['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-1">{{ $catLabel }}</h5>
                                @if($soon)
                                    <p class="text-muted small mb-3">New pieces are on the way — check back soon!</p>
                                @else
                                <p class="text-muted small mb-3" data-live-price data-live-prefix="From ">@if($cheapest)<x-price :variant="$cheapest" prefix="From " />@else{{ $pDesc }}@endif</p>
                                @endif
                                <button class="btn cat-explore-btn w-100 py-2 text-white"
                                        style="background:{{ $cfg['btnBg'] }};">
                                    @if($soon) Coming Soon @else Explore {{ $catLabel }} @endif
                                    <i class="ri-arrow-right-line ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @empty
                {{-- Fallback to static cards if DB is empty --}}
                @php
                    $staticCats = [
                        ['name'=>'Mystery Scoops','link'=>'mystery-scoops','icon'=>'ri-gift-line',
                         'overlay'=>'linear-gradient(to top,rgba(236,72,153,.7),rgba(251,113,133,.4))',
                         'iconBg' =>'linear-gradient(135deg,#f472b6,#fb7185)',
                         'btnBg'  =>'linear-gradient(135deg,#f472b6,#fb7185)',
                         'image'  =>'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=400&q=80',
                         'desc'   =>'Curated mystery items in 3 sizes'],
                        ['name'=>'Mystery Balls','link'=>'mystery-balls','icon'=>'ri-sparkling-line',
                         'overlay'=>'linear-gradient(to top,rgba(168,85,247,.7),rgba(236,72,153,.4))',
                         'iconBg' =>'linear-gradient(135deg,#c084fc,#f472b6)',
                         'btnBg'  =>'linear-gradient(135deg,#f472b6,#fb7185)',
                         'image'  =>'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=400&q=80',
                         'desc'   =>'Fun mystery balls, minimum 5 pcs'],
                        ['name'=>'BTS Merch','link'=>'bts-merch','icon'=>'ri-star-line',
                         'overlay'=>'linear-gradient(to top,rgba(124,58,237,.7),rgba(167,139,250,.4))',
                         'iconBg' =>'linear-gradient(135deg,#818cf8,#a78bfa)',
                         'btnBg'  =>'linear-gradient(135deg,#C084FC,#9333EA)',
                         'image'  =>'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=400&q=80',
                         'desc'   =>'Exclusive ARMY collection 💜'],
                        ['name'=>'Gift Boxes','link'=>'gift-boxes','icon'=>'ri-shopping-bag-line',
                         'overlay'=>'linear-gradient(to top,rgba(244,63,94,.7),rgba(251,113,133,.4))',
                         'iconBg' =>'linear-gradient(135deg,#fb7185,#f472b6)',
                         'btnBg'  =>'linear-gradient(135deg,#fb7185,#f472b6)',
                         'image'  =>'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=400&q=80',
                         'desc'   =>'Themed gift boxes for all occasions'],
                        ['name'=>'BTS Gift Boxes','link'=>'bts-gift-boxes','icon'=>'ri-gift-2-line',
                         'overlay'=>'linear-gradient(to top,rgba(124,58,237,.7),rgba(167,139,250,.4))',
                         'iconBg' =>'linear-gradient(135deg,#818cf8,#a78bfa)',
                         'btnBg'  =>'linear-gradient(135deg,#C084FC,#9333EA)',
                         'image'  =>'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=400&q=80',
                         'desc'   =>'BTS era & BT21 themed gift boxes 💜'],
                        ['name'=>'Ceramic Items','link'=>'ceramic-items','icon'=>'ri-cup-line',
                         'overlay'=>'linear-gradient(to top,rgba(139,94,60,.5),rgba(200,169,138,.4))',
                         'iconBg' =>'linear-gradient(135deg,#C8A98A,#8B5E3C)',
                         'btnBg'  =>'linear-gradient(135deg,#C8A98A,#8B5E3C)',
                         'image'  =>'https://images.unsplash.com/photo-1515401857507-c897a8f64e42?w=400&q=80',
                         'desc'   =>'Cute ceramic mugs & kitchen items'],
                    ];
                @endphp
                @foreach($staticCats as $cat)
                <div class="col-md-6 col-lg-4 fade-up">
                    <a href="{{ url($cat['link']) }}" class="text-decoration-none">
                        <div class="cat-card card">
                            <div class="cat-card-img-wrap">
                                <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}">
                                <div class="cat-card-overlay" style="background:{{ $cat['overlay'] }};"></div>
                                <div class="cat-card-icon-wrap" style="background:{{ $cat['iconBg'] }};">
                                    <i class="{{ $cat['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-1">{{ $cat['name'] }}</h5>
                                <p class="text-muted small mb-3">{{ $cat['desc'] }}</p>
                                <button class="btn cat-explore-btn w-100 py-2 text-white"
                                        style="background:{{ $cat['btnBg'] }};">
                                    Explore {{ $cat['name'] }}
                                    <i class="ri-arrow-right-line ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         CTA
    ═══════════════════════════════════════════ --}}
    <section class="cta-section py-5 text-center fade-up">
        <div class="container py-4">
            <i class="ri-sparkling-2-line cta-pulse d-block mb-4" style="font-size:3.5rem;"></i>
            <h2 class="fw-bold display-6 mb-3 text-white ">Ready for Your Mystery Adventure?</h2>
            <p class="fs-5 mb-4 mx-auto" style="max-width:36rem;color:rgba(255,255,255,.9);">
                Join thousands of happy customers who love the thrill of mystery shopping.
                Every order is carefully curated with love! 💖
            </p>
            <a href="{{ url('/shop') }}"
               class="btn btn-light fw-bold px-5 py-3  shadow-lg"
               style="color:#7c3aed;font-size:1.05rem; border-radius:12px;">
                Start Shopping Now <i class="ri-arrow-right-line ms-1"></i>
            </a>
        </div>
    </section>

</div>

@endsection

@section('script')
<script>
    // Scroll-triggered fade-up
   document.addEventListener('DOMContentLoaded', function () {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(e => { 
            if (e.isIntersecting) e.target.classList.add('visible'); 
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
});
</script>
@endsection
