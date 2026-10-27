@extends('layouts.landing-page-layout')
@section('title', ['bts-gift-box' => 'BTS Gift Boxes', 'ceramic' => 'Ceramic Collection'][$section ?? 'gift-box'] ?? 'Mystery Gift Boxes')

@section('css')
<style>
    .cat-wrapper { background: linear-gradient(to bottom,#fdf2f8,#f5f3ff); min-height: 100vh; }

    /* ── Header ── */
    .cat-header-icon {
        width: 4rem; height: 4rem; border-radius: 50%;
        background: #fce7f3;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem; color: #ec4899;
    }

    /* ── Product grid cards ── */
    .gb-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        height: 100%;
        display: flex; flex-direction: column;
    }
    /* Hover lift only where there is a real hover — on a phone it sticks to the last card tapped */
    @media (hover: hover) {
        .gb-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }
        .gb-card:hover .gb-card-img { transform: scale(1.08); }
    }

    .gb-card-img-wrap { position: relative; overflow: hidden; height: 13rem; flex-shrink: 0; }
    .gb-card-img {
        width: 100%; height: 100%; object-fit: cover;
        display: block; transition: transform .5s;
    }
    .gb-card-body { padding: 1rem; flex: 1; display: flex; flex-direction: column; }

    .gb-badge {
        position: absolute; top: .75rem; left: .75rem;
        font-size: .7rem; font-weight: 700; padding: .2rem .6rem;
        border-radius: .4rem; color: #fff;
    }

    .gb-heart-btn {
        position: absolute; top: .6rem; right: .6rem;
        width: 2.1rem; height: 2.1rem; border-radius: 50%;
        background: rgba(255,255,255,.85); border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 5;
        transition: transform .2s, background .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .gb-heart-btn:hover { transform: scale(1.15); background: #fff; }
    .gb-heart-btn i { font-size: 1.1rem; color: #d1d5db; pointer-events: none; }
    .gb-heart-btn.active i { color: #ef4444; }

    /* Names and descriptions take at most two lines, with room for two, so cards in a row line up */
    .gb-name {
        font-weight: 700; font-size: .95rem; line-height: 1.3;
        color: #1f2937; margin-bottom: .2rem; min-height: 2.6em;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .gb-desc {
        font-size: .8rem; line-height: 1.35; color: #6b7280; margin-bottom: .4rem; min-height: 2.7em;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .gb-price {
        font-weight: 700; font-size: .9rem;
        background: linear-gradient(to right,#ec4899,#a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: .75rem;
    }
    .gb-add-btn {
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .85rem; padding: .55rem 1rem;
        transition: opacity .2s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .35rem;
        white-space: nowrap; margin-top: auto;   /* "View Product" stays on one line, pinned to the card's bottom */
        text-decoration: none !important;
    }
    .gb-add-btn:hover { opacity: .85; color: #fff; text-decoration: none !important; }

    /* ── Filter bar ── */
    .filter-btn {
        border: 1.5px solid #e5e7eb; border-radius: 2rem;
        background: #fff; color: #374151;
        font-size: .8rem; font-weight: 500; padding: .35rem 1rem;
        transition: all .2s; cursor: pointer;
    }
    .filter-btn:hover, .filter-btn.active {
        border-color: #a855f7; background: #ede9fe; color: #7c3aed; font-weight: 600;
    }

    /* ── BTS Gift Boxes (BTS Merch theme) ── */
    .bts-gb-wrapper { background: #fff; min-height: 100vh; }
    .bts-gb-hero {
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);
        color: #fff;
        padding: 4rem 0 0;
        position: relative;
    }
    .bts-gb-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 2rem; padding: .3rem .9rem;
        font-size: .8rem; font-weight: 500; margin-bottom: 1.25rem;
    }
    .bts-gb-badge i { color: #f9a8d4; }
    .bts-gb-price {
        font-weight: 700; font-size: .9rem;
        background: linear-gradient(to right,#7c3aed,#a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: .75rem;
    }
    .bts-gb-explore-btn {
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .85rem; padding: .55rem 1rem;
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        transition: opacity .2s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .35rem;
        white-space: nowrap; margin-top: auto;
        text-decoration: none !important;
    }
    .bts-gb-explore-btn:hover { opacity: .85; color: #fff; text-decoration: none !important; }

    /* ── Phones: two compact, even cards per row ── */
    @media (max-width: 575.98px) {
        .gb-card { border-radius: .85rem; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .gb-card-img-wrap { height: auto; aspect-ratio: 1 / 1; }
        .gb-badge { top: .5rem; left: .5rem; font-size: .6rem; padding: .15rem .45rem; max-width: calc(100% - 3rem); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .gb-heart-btn { top: .5rem; right: .5rem; width: 1.9rem; height: 1.9rem; }
        .gb-card-img-wrap .discount-badge { left: .5rem !important; bottom: .5rem !important; font-size: .65rem !important; padding: .3rem .5rem !important; }
        .gb-card-body { padding: .7rem .7rem .8rem; }
        .gb-name { font-size: .85rem; }
        .gb-desc { font-size: .72rem; }
        .gb-price, .bts-gb-price { font-size: .8rem; margin-bottom: .6rem; }
        .gb-price .price-was, .bts-gb-price .price-was { white-space: nowrap; }
        .gb-add-btn, .bts-gb-explore-btn { font-size: .8rem; padding: .5rem .4rem; gap: .2rem; }
        .gb-add-btn i, .bts-gb-explore-btn i { margin-left: 0 !important; }
    }
</style>
@endsection

@section('content')
@php
    $fallbackImage = 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80';

    // Live updates: this page's product set, so adding / removing a product refreshes it (layouts/live-updates)
    $liveListing   = 'category:' . ($dbCategories ?? collect())->pluck('name')->implode('|');
    $liveSignature = app(\App\Services\LiveUpdateService::class)->listingSignature($liveListing);

    // Flatten all products from the loaded DB categories
    $allDbProducts = collect();
    foreach (($dbCategories ?? collect()) as $dbCat) {
        // Direct products (no sub-category) — a product's category relation also
        // matches products that belong to one of its sub-categories, so only
        // take the ones with no sub_category_id here to avoid double-counting.
        foreach ($dbCat->products as $p) {
            if (is_null($p->sub_category_id)) {
                $allDbProducts->push($p);
            }
        }
        // Products under sub-categories
        foreach ($dbCat->subCategories as $sub) {
            foreach ($sub->products as $p) {
                $p->_sub_name = $sub->name; // attach sub-category name for tags/filter
                $allDbProducts->push($p);
            }
        }
    }

    // ── Gift-box filter tag map (sub-category name → filter tag) ──────────────
    $giftTagMap = [
        'Kuromi'       => 'sanrio',
        'Hello Kitty'  => 'sanrio',
        'Cinnamoroll'  => 'sanrio',
        'My Melody'    => 'sanrio',
        'Tom & Jerry'  => 'cartoon',
        'Ben 10'       => 'cartoon',
        'Pokémon'      => 'cartoon',
        'Harry Potter' => 'cartoon',
    ];

    // ── BTS gift-box filter tag map (sub-category name → filter tag) ──────────
    $btsGiftTagMap = [
        'Era Boxes'   => 'era',
        'BT21 World'  => 'bt21',
        'OT7 Special' => 'ot7',
    ];

    // ── Ceramic filter tag map (sub-category name → filter tag) ──────────────
    $ceramicTagMap = [
        'Mugs'   => 'mug',
        'Bowls'  => 'bowl',
        'Spoons' => 'set',
    ];
@endphp

@if(($section ?? 'gift-box') === 'gift-box')
<div class="cat-wrapper pb-5 rv-pt-back">
<div class="container px-3 px-sm-4">

    {{-- ── Header ── --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold mb-3"
            style="font-size:clamp(2rem,5vw,3rem);background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Mystery Gift Boxes
            <button class="btn p-0 ms-2 border-0 bg-transparent align-middle" style="font-size:1.5rem;color:#d1d5db;" title="Add to wishlist">
                <i class="ri-heart-line"></i>
            </button>
        </h1>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1rem;">
            The perfect surprise for yourself or a loved one! Choose your favorite character theme and receive a curated box of mystery items.
        </p>
    </div>

    {{-- ── Filter bar ── --}}
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
        <button class="filter-btn active" onclick="filterBoxes('all', this)">All</button>
        <button class="filter-btn" onclick="filterBoxes('sanrio', this)">Sanrio Characters</button>
        <button class="filter-btn" onclick="filterBoxes('cartoon', this)">Cartoon Special</button>
    </div>

    {{-- ── Grid ── --}}
    <div class="row g-2 g-sm-4" id="gb-grid" data-live-listing="{{ $liveListing }}" data-live-signature="{{ $liveSignature }}">
        @forelse($allDbProducts as $product)
        @php
            $subName   = $product->_sub_name ?? $product->subCategory?->name ?? '';
            $tag       = $giftTagMap[$subName] ?? 'bts';
            $image     = $product->images->first()?->image_url ?? $fallbackImage;
            $cheapest  = $product->cheapestVariant();
            $priceLabel = $cheapest ? 'From PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
        @endphp
        <div class="col-6 col-md-4 gb-item" data-tag="{{ $tag }}" data-live-product="{{ $product->id }}">
            <div class="gb-card">
                <div class="gb-card-img-wrap">
                    <img src="{{ $image }}" alt="{{ $product->name }}" class="gb-card-img" data-live-image>
                    <x-discount-badge :label="$product->discountLabel()" live />
                    @if($subName)
                        <span class="gb-badge" style="background:#ec4899;">{{ $subName }}</span>
                    @endif
                    <button class="gb-heart-btn"
                            data-id="p-{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $priceLabel }}"
                            data-image="{{ $image }}"
                            onclick="toggleWishlistGb(this)"
                            title="Add to Wishlist">
                        <i class="ri-heart-line"></i>
                    </button>
                </div>
                <div class="gb-card-body">
                    <h6 class="gb-name mb-1" data-live-name>{{ $product->name }}</h6>
                    <p class="gb-desc">{{ $product->description }}</p>
                    <p class="gb-price" data-live-price data-live-prefix="From "><x-price :variant="$cheapest" prefix="From " /></p>
                    @if($product->variants->count() > 1)
                        <p class="text-muted mb-2" style="font-size:.75rem;">{{ $product->variants->count() }} variants available</p>
                    @endif
                    <a href="{{ route('product-item', $product->slug) }}" class="gb-add-btn" style="background:linear-gradient(135deg,#f472b6,#ec4899);text-decoration:none!important;">
                        View Product <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="ri-gift-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No gift boxes found. Please run the database seeder.</p>
        </div>
        @endforelse
    </div>

    <div id="gb-no-results" class="text-center py-5" style="display:none;">
        <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
        <p class="text-muted mt-2">No gift boxes found for this filter.</p>
    </div>

</div>
</div>
@endif

@if(($section ?? 'gift-box') === 'bts-gift-box')
{{-- ══════════════════════════════════════════════════════
     BTS GIFT BOXES SECTION (BTS Merch theme)
══════════════════════════════════════════════════════ --}}

<div class="bts-gb-wrapper">

    {{-- ── Hero ── --}}
    <div class="bts-gb-hero text-center">
        <div class="container px-4 position-relative" style="z-index:1;">
            <div class="bts-gb-badge">
                <i class="ri-heart-fill"></i> ARMY Exclusive Collection
            </div>
            <h1 class="fw-bold mb-3 text-white" style="font-size:clamp(2rem,5vw,3rem);">BTS Gift Boxes</h1>
            <p class="mb-4 mx-auto" style="max-width:34rem;color:rgba(255,255,255,.9);font-size:1.05rem;">
                Curated BTS-themed gift boxes for every era and every bias. Borahae! 💜
            </p>
        </div>

        {{-- Wave --}}
        <div style="line-height:0;margin-top:3rem;margin-bottom:-2px;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;display:block;">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="#fff"/>
            </svg>
        </div>
    </div>

    <div class="container px-3 px-sm-4 pb-5">

        {{-- ── Filter bar ── --}}
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <button class="filter-btn active" onclick="filterBtsGiftBoxes('all', this)">All</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('era', this)">Era Boxes</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('bt21', this)">BT21 World</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('ot7', this)">OT7 Special</button>
        </div>

        {{-- ── Grid ── --}}
        <div class="row g-2 g-sm-4" id="bts-gb-grid" data-live-listing="{{ $liveListing }}" data-live-signature="{{ $liveSignature }}">
            @forelse($allDbProducts as $product)
            @php
                $subName    = $product->_sub_name ?? $product->subCategory?->name ?? '';
                $tag        = $btsGiftTagMap[$subName] ?? 'era';
                $image      = $product->images->first()?->image_url ?? $fallbackImage;
                $cheapest   = $product->cheapestVariant();
                $priceLabel = $cheapest ? 'From PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
            @endphp
            <div class="col-6 col-md-4 bts-gb-item" data-tag="{{ $tag }}" data-live-product="{{ $product->id }}">
                <div class="gb-card">
                    <div class="gb-card-img-wrap">
                        <img src="{{ $image }}" alt="{{ $product->name }}" class="gb-card-img" data-live-image>
                        <x-discount-badge :label="$product->discountLabel()" live />
                        @if($subName)
                            <span class="gb-badge" style="background:#7c3aed;">{{ $subName }}</span>
                        @endif
                        <button class="gb-heart-btn"
                                data-id="p-{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-price="{{ $priceLabel }}"
                                data-image="{{ $image }}"
                                onclick="toggleWishlistGb(this)"
                                title="Add to Wishlist">
                            <i class="ri-heart-line"></i>
                        </button>
                    </div>
                    <div class="gb-card-body">
                        <h6 class="gb-name mb-1" data-live-name>{{ $product->name }}</h6>
                        <p class="gb-desc">{{ $product->description }}</p>
                        <p class="bts-gb-price" data-live-price data-live-prefix="From "><x-price :variant="$cheapest" prefix="From " /></p>
                        @if($product->variants->count() > 1)
                            <p class="text-muted mb-2" style="font-size:.75rem;">{{ $product->variants->count() }} variants available</p>
                        @endif
                        <a href="{{ route('product-item', $product->slug) }}" class="bts-gb-explore-btn">
                            View Product <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="ri-gift-line" style="font-size:2.5rem;color:#d1d5db;"></i>
                <p class="text-muted mt-2">No BTS gift boxes found. Please run the database seeder.</p>
            </div>
            @endforelse
        </div>

        <div id="bts-gb-no-results" class="text-center py-5" style="display:none;">
            <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No BTS gift boxes found for this filter.</p>
        </div>

    </div>
</div>
@endif

@if(($section ?? 'gift-box') === 'ceramic')
{{-- ══════════════════════════════════════════════════════
     CERAMIC COLLECTION SECTION
══════════════════════════════════════════════════════ --}}

<div id="ceramic-collection" style="background:linear-gradient(to bottom,#fdf8f0,#f5ede0);padding:4rem 0 3rem;">
<div class="container px-3 px-sm-4">

    {{-- Ceramic Header --}}
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3rem);color:#5c3d1e;">
            Ceramic Collection
        </h2>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1rem;">
            Handpicked, aesthetic ceramic pieces to brighten up your home and daily routine.
        </p>
    </div>

    @if(\App\Models\Category::nameIsComingSoon('Ceramic Items'))
        {{-- Nothing on sale yet: no filters, no products (see Category::COMING_SOON) --}}
        <x-coming-soon title="Our ceramic pieces are on the way"
                       message="We're putting the finishing touches on our mugs, bowls and spoons. Check back soon!" />
    @else

    {{-- Ceramic Filters --}}
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
        <button class="cer-filter-btn active" onclick="filterCeramics('all',this)"
                style="border:1.5px solid #e8d5c0;border-radius:2rem;background:#fff;color:#374151;font-size:.8rem;font-weight:500;padding:.35rem 1rem;transition:all .2s;cursor:pointer;">All</button>
        <button class="cer-filter-btn" onclick="filterCeramics('mug',this)"
                style="border:1.5px solid #e8d5c0;border-radius:2rem;background:#fff;color:#374151;font-size:.8rem;font-weight:500;padding:.35rem 1rem;transition:all .2s;cursor:pointer;">Mugs</button>
        <button class="cer-filter-btn" onclick="filterCeramics('bowl',this)"
                style="border:1.5px solid #e8d5c0;border-radius:2rem;background:#fff;color:#374151;font-size:.8rem;font-weight:500;padding:.35rem 1rem;transition:all .2s;cursor:pointer;">Bowls</button>
        <button class="cer-filter-btn" onclick="filterCeramics('set',this)"
                style="border:1.5px solid #e8d5c0;border-radius:2rem;background:#fff;color:#374151;font-size:.8rem;font-weight:500;padding:.35rem 1rem;transition:all .2s;cursor:pointer;">Spoons & Sets</button>
    </div>

    {{-- Ceramic Grid --}}
    <div class="row g-2 g-sm-4" id="cer-grid" data-live-listing="{{ $liveListing }}" data-live-signature="{{ $liveSignature }}">
        @forelse($allDbProducts as $product)
        @php
            $subName    = $product->_sub_name ?? $product->subCategory?->name ?? '';
            $tag        = $ceramicTagMap[$subName] ?? 'mug';
            $image      = $product->images->first()?->image_url ?? $fallbackImage;
            $cheapest   = $product->cheapestVariant();
            $priceLabel = $cheapest ? 'PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
            $variantName = $product->variants->first()?->variant_name;
        @endphp
        <div class="col-6 col-md-4 cer-item" data-tag="{{ $tag }}" data-live-product="{{ $product->id }}">
            <div class="gb-card">
                <div class="gb-card-img-wrap">
                    <img src="{{ $image }}" alt="{{ $product->name }}" class="gb-card-img" data-live-image>
                    <x-discount-badge :label="$product->discountLabel()" live />
                    @if($subName)
                        <span class="gb-badge" style="background:#C8A98A;">{{ $subName }}</span>
                    @endif
                    <button class="gb-heart-btn"
                            data-id="p-{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $priceLabel }}"
                            data-image="{{ $image }}"
                            onclick="toggleWishlistGb(this)"
                            title="Add to Wishlist">
                        <i class="ri-heart-line"></i>
                    </button>
                </div>
                <div class="gb-card-body">
                    <h6 class="gb-name mb-1" data-live-name>{{ $product->name }}</h6>
                    <p class="gb-desc">{{ $product->description }}</p>
                    <p class="gb-price" data-live-price><x-price :variant="$cheapest" /></p>
                    @if($variantName)
                        <p class="text-muted mb-2" style="font-size:.75rem;">{{ $variantName }}</p>
                    @endif
                    <a href="{{ route('product-item', $product->slug) }}" class="gb-add-btn" style="background:linear-gradient(135deg,#C8A98A,#8B5E3C);text-decoration:none!important;">
                        View Product <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="ri-cup-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No ceramic items found. Please run the database seeder.</p>
        </div>
        @endforelse
    </div>

    <div id="cer-no-results" class="text-center py-5" style="display:none;">
        <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
        <p class="text-muted mt-2">No ceramic items found for this filter.</p>
    </div>

    @endif

</div>
</div>
@endif
@endsection

@section('script')
<script>
// ── Filter ───────────────────────────────────────────────────────────────
function filterBoxes(tag, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const items = document.querySelectorAll('.gb-item');
    let visible = 0;
    items.forEach(item => {
        const match = tag === 'all' || item.dataset.tag === tag;
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('gb-no-results').style.display = visible === 0 ? 'block' : 'none';
    rememberCategoryFilter(tag);
}

// The chosen filter lives in the URL (?filter=…) so Back from a product page returns to it
function rememberCategoryFilter(tag) {
    history.replaceState(null, '', location.pathname + (tag === 'all' ? '' : '?filter=' + encodeURIComponent(tag)));
}

// ── BTS Gift Boxes Filter ───────────────────────────────────────────────────
function filterBtsGiftBoxes(tag, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const items = document.querySelectorAll('.bts-gb-item');
    let visible = 0;
    items.forEach(item => {
        const match = tag === 'all' || item.dataset.tag === tag;
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('bts-gb-no-results').style.display = visible === 0 ? 'block' : 'none';
    rememberCategoryFilter(tag);
}

// ── Wishlist ─────────────────────────────────────────────────────────────
function toggleWishlistGb(btn) {
    const id    = btn.dataset.id;
    const name  = btn.dataset.name;
    const price = btn.dataset.price;
    const image = btn.dataset.image;

    fetch('{{ route('wish-list.toggle') }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ id, name, price, image })
    })
    .then(r => r.json())
    .then(data => {
        const icon = btn.querySelector('i');
        if (data.inWishlist) {
            btn.classList.add('active');
            icon.classList.replace('ri-heart-line','ri-heart-fill');
        } else {
            btn.classList.remove('active');
            icon.classList.replace('ri-heart-fill','ri-heart-line');
        }
        const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
        if (data.inWishlist) saved[id] = true; else delete saved[id];
        localStorage.setItem('rv_wishlist', JSON.stringify(saved));

        if (typeof updateWishlistBadge === 'function') updateWishlistBadge(data.count);
    });
}

// ── Add to Cart ──────────────────────────────────────────────────────────
function addGbToCart(id, name, price, image) {
    const numericPrice = parseInt(price.replace(/[^0-9]/g,'')) || 0;
    const cart = JSON.parse(localStorage.getItem('rv_cart') || '[]');
    const existing = cart.find(i => i.id === id);
    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({ id, name, subtitle: 'Gift Box', image, unitPrice: numericPrice, qty: 1, preorder: false });
    }
    localStorage.setItem('rv_cart', JSON.stringify(cart));
    window.location.href = '{{ url('/cart') }}';
}

// ── Ceramic Filter ────────────────────────────────────────────────────────
function filterCeramics(tag, btn) {
    document.querySelectorAll('.cer-filter-btn').forEach(b => {
        b.style.borderColor = '#e8d5c0';
        b.style.background  = '#fff';
        b.style.color       = '#374151';
        b.style.fontWeight  = '500';
    });
    btn.style.borderColor = '#8B5E3C';
    btn.style.background  = '#f9f5f0';
    btn.style.color       = '#8B5E3C';
    btn.style.fontWeight  = '600';

    const items = document.querySelectorAll('.cer-item');
    let visible = 0;
    items.forEach(item => {
        const match = tag === 'all' || item.dataset.tag === tag;
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('cer-no-results').style.display = visible === 0 ? 'block' : 'none';
}

// Init ceramic filter active state + restore wishlist states
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.cer-filter-btn');
    if (first) {
        first.style.borderColor = '#8B5E3C';
        first.style.background  = '#f9f5f0';
        first.style.color       = '#8B5E3C';
        first.style.fontWeight  = '600';
    }

    // Back from a product page: re-apply the filter chip that was chosen
    const savedFilter = new URLSearchParams(location.search).get('filter');
    if (savedFilter) {
        const chip = [...document.querySelectorAll('.filter-btn')].find(b => (b.getAttribute('onclick') || '').includes(`'${savedFilter}'`));
        if (chip) chip.click();
    }

    const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
    document.querySelectorAll('.gb-heart-btn').forEach(btn => {
        if (saved[btn.dataset.id]) {
            btn.classList.add('active');
            btn.querySelector('i').classList.replace('ri-heart-line','ri-heart-fill');
        }
    });
});
</script>
@endsection
