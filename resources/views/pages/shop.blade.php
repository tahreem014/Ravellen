@extends('layouts.landing-page-layout')

@section('css')
<style>
    .shop-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    /* ── Search ── */
    .shop-search-wrap {
        max-width: 28rem;
        margin: 0 auto;
        position: relative;
    }
    .shop-search-wrap input {
        border-radius: 2rem;
        border: 1px solid #e9d5ff;
        padding: .6rem 1.25rem .6rem 1.25rem;
        width: 100%;
        font-size: .9rem;
        outline: none;
        box-shadow: 0 2px 12px rgba(168,85,247,.08);
    }
    .shop-search-wrap input:focus { border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168,85,247,.15); }
    .shop-search-wrap .search-icon {
        position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
        color: #a855f7; font-size: 1.1rem; pointer-events: none;
    }

    /* ── Category / Sub-category pills ── */
    .shop-pill {
        border: 1.5px solid #e5e7eb; border-radius: 2rem;
        background: #fff; color: #374151;
        font-size: .8rem; font-weight: 500; padding: .4rem 1.1rem;
        transition: all .2s; cursor: pointer;
    }
    .shop-pill:hover, .shop-pill.active {
        border-color: #a855f7; background: #ede9fe; color: #7c3aed; font-weight: 600;
    }
    .shop-pill.sub { font-size: .75rem; padding: .3rem .9rem; }

    /* ── Product Cards ── */
    .shop-prod-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        height: 100%;
    }
    .shop-prod-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }
    .shop-prod-img-wrap { position: relative; height: 11rem; overflow: hidden; }
    .shop-prod-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        display: block; transition: transform .5s;
    }
    .shop-prod-card:hover .shop-prod-img-wrap img { transform: scale(1.08); }
    .shop-prod-badge {
        position: absolute; top: .6rem; left: .6rem;
        color: #fff;
        font-size: .65rem; font-weight: 700;
        padding: .18rem .55rem; border-radius: .35rem;
    }
    .shop-prod-cat-label {
        font-size: .68rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .03em; margin-bottom: .15rem;
    }
    .shop-prod-name {
        font-weight: 700; font-size: .9rem; color: #1f2937; margin-bottom: .3rem;
    }
    .shop-prod-price {
        font-weight: 700; font-size: .85rem;
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: .75rem;
    }
    .shop-explore-btn {
        border: 0; color: #fff; font-weight: 600; font-size: .85rem;
        border-radius: .5rem; padding: .55rem 1rem;
        transition: opacity .2s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .35rem;
        text-decoration: none !important;
    }
    .shop-explore-btn:hover { opacity: .85; color: #fff; text-decoration: none !important; }

    /* ── Pagination ── */
    .shop-page-btn {
        border: 1.5px solid #e5e7eb; border-radius: .5rem;
        background: #fff; color: #374151;
        font-size: .85rem; font-weight: 600; padding: .4rem .85rem;
        min-width: 2.4rem;
        transition: all .2s; cursor: pointer;
    }
    .shop-page-btn:hover:not(:disabled):not(.active) {
        border-color: #a855f7; background: #ede9fe; color: #7c3aed;
    }
    .shop-page-btn.active {
        border-color: transparent;
        background: linear-gradient(135deg,#9333ea,#ec4899);
        color: #fff;
    }
    .shop-page-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* ── CTA ── */
    .shop-cta {
        background: linear-gradient(135deg, #ec4899 0%, #a855f7 100%);
        border-radius: 1.5rem;
        color: #fff;
    }

    /* ── Heart / Wishlist ── */
    .wl-heart-btn {
        position: absolute;
        top: .6rem;
        right: .6rem;
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: rgba(255,255,255,.85);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: transform .2s, background .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .wl-heart-btn:hover { transform: scale(1.15); background: #fff; }
    .wl-heart-btn i {
        font-size: 1.1rem;
        color: #d1d5db;
        transition: color .2s;
        pointer-events: none;
    }
    .wl-heart-btn.active i { color: #ef4444; }
</style>
@endsection

@section('content')

<div class="shop-wrapper">
    <div class="container px-4 py-5">

        {{-- ── Header ── --}}
        <div class="text-center mb-4 fade-up">
            <h1 class="fw-bold mb-2"
                style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,#9333ea,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Shop All
            </h1>
            <p class="text-muted mb-4">Explore our full collection of mystery items, BTS merch, and unique gifts</p>

            <div class="shop-search-wrap">
                <input type="text" id="shop-search" placeholder="Search by category, sub-category, or product name" oninput="filterShop(this.value)">
                <i class="ri-search-line search-icon"></i>
            </div>
        </div>

        @php
            $fallbackImage = 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=600&q=80';

            // Per-category accent theme, matching the product-detail/category pages
            // (Mystery Scoops/Balls and Gift Boxes match the exact gradients used on the homepage cards)
            $shopThemeMap = [
                'Mystery Scoops' => ['accent' => '#f472b6', 'accent2' => '#fb7185', 'grad' => 'linear-gradient(135deg,#f472b6,#fb7185)'],
                'Mystery Balls'  => ['accent' => '#f472b6', 'accent2' => '#fb7185', 'grad' => 'linear-gradient(135deg,#f472b6,#fb7185)'],
                'BTS Merch'      => ['accent' => '#7c3aed', 'accent2' => '#a855f7', 'grad' => 'linear-gradient(135deg,#7c3aed,#a855f7)'],
                'BTS Gift Boxes' => ['accent' => '#7c3aed', 'accent2' => '#a855f7', 'grad' => 'linear-gradient(135deg,#7c3aed,#a855f7)'],
                'Gift Boxes'     => ['accent' => '#fb7185', 'accent2' => '#f472b6', 'grad' => 'linear-gradient(135deg,#fb7185,#f472b6)'],
                'Ceramic Items'  => ['accent' => '#8B5E3C', 'accent2' => '#C8A98A', 'grad' => 'linear-gradient(135deg,#C8A98A,#8B5E3C)'],
            ];
            $shopDefaultTheme = ['accent' => '#9333ea', 'accent2' => '#ec4899', 'grad' => 'linear-gradient(135deg,#9333ea,#ec4899)'];

            // Flatten every product across every category/sub-category, tagging
            // each with its parent category & sub-category for filtering.
            $allProducts = collect();
            foreach ($categories as $category) {
                foreach ($category->products->whereNull('sub_category_id') as $p) {
                    $p->_cat = $category;
                    $p->_sub = null;
                    $allProducts->push($p);
                }
                foreach ($category->subCategories as $sub) {
                    foreach ($sub->products as $p) {
                        $p->_cat = $category;
                        $p->_sub = $sub;
                        $allProducts->push($p);
                    }
                }
            }
        @endphp

        {{-- ── Category filter pills ── --}}
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-3" id="shop-cat-pills">
            <button type="button" class="shop-pill active" data-cat="all" onclick="selectShopCategory('all', this)">All Categories</button>
            @foreach($categories as $category)
                <button type="button" class="shop-pill" data-cat="{{ $category->id }}" onclick="selectShopCategory('{{ $category->id }}', this)">{{ $category->name }}</button>
            @endforeach
        </div>

        {{-- ── Sub-category filter pills (shown once a category with sub-categories is selected) ── --}}
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4" id="shop-sub-pills" style="display:none;">
            <button type="button" class="shop-pill sub active" data-sub="all" data-parent="all" onclick="selectShopSub('all', this)">All</button>
            @foreach($categories as $category)
                @foreach($category->subCategories as $sub)
                    <button type="button" class="shop-pill sub" data-sub="{{ $sub->id }}" data-parent="{{ $category->id }}" style="display:none;" onclick="selectShopSub('{{ $sub->id }}', this)">{{ $sub->name }}</button>
                @endforeach
            @endforeach
        </div>

        {{-- ── Product grid ── --}}
        <div class="row g-3" id="shop-grid">
            @forelse($allProducts as $product)
                @php
                    $cat        = $product->_cat;
                    $sub        = $product->_sub;
                    $image      = $product->images->first()?->image_url ?? $fallbackImage;
                    $minPrice   = $product->variants->min('price');
                    $priceLabel = $minPrice ? 'From PKR ' . number_format($minPrice) : 'PKR —';
                    $searchText = strtolower($cat->name . ' ' . ($sub->name ?? '') . ' ' . $product->name);
                    $theme      = $shopThemeMap[$cat->name] ?? $shopDefaultTheme;
                @endphp
                <div class="col-6 col-md-4 col-lg-3 shop-item"
                     data-cat="{{ $cat->id }}"
                     data-sub="{{ $sub->id ?? '' }}"
                     data-search="{{ $searchText }}">
                    <div class="shop-prod-card">
                        <div class="shop-prod-img-wrap">
                            <img src="{{ $image }}" alt="{{ $product->name }}">
                            @if($sub)
                                <span class="shop-prod-badge" style="background:{{ $theme['accent'] }};">{{ $sub->name }}</span>
                            @endif
                            <button class="wl-heart-btn"
                                    data-id="p-{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $priceLabel }}"
                                    data-image="{{ $image }}"
                                    onclick="toggleWishlist(this, event)"
                                    title="Add to Wishlist">
                                <i class="ri-heart-line"></i>
                            </button>
                        </div>
                        <div class="p-3">
                            <p class="shop-prod-cat-label" style="color:{{ $theme['accent'] }};">{{ $cat->name }}</p>
                            <h6 class="shop-prod-name">{{ $product->name }}</h6>
                            <p class="shop-prod-price" style="background:linear-gradient(to right,{{ $theme['accent'] }},{{ $theme['accent2'] }});-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $priceLabel }}</p>
                            <a href="{{ route('product-item', $product->slug) }}" class="shop-explore-btn" style="background:{{ $theme['grad'] }};">
                                View Product <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
                    <p class="text-muted mt-2">No products found. Please run the database seeder.</p>
                </div>
            @endforelse
        </div>
        <div id="no-results" class="text-center py-5" style="display:none;">
            <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No products found. Try a different search or filter.</p>
        </div>

        {{-- ── Pagination ── --}}
        <div id="shop-pagination" class="d-flex flex-wrap gap-2 justify-content-center mt-4"></div>

        {{-- ── CTA ── --}}
        <div class="shop-cta text-center p-5 mt-5 fade-up">
            <h2 class="fw-bold mb-3 text-white">Can't Decide?</h2>
            <p class="mb-4 mx-auto" style="max-width:32rem;color:rgba(255,255,255,.9);">
                Try our Mystery Scoops! Each scoop is a surprise adventure with curated items perfect for you or as gifts.
            </p>
            <a href="{{ url('/mystery-scoops') }}"
               class="btn btn-light fw-semibold px-5 py-2"
               style="color:#7c3aed;border-radius:.5rem;">
                Get Mystery Scoop
            </a>
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
const SHOP_PAGE_SIZE = 12;

let shopActiveCat = 'all';
let shopActiveSub = 'all';
let shopSearchQuery = '';
let shopCurrentPage = 1;

// ── Category selection ──────────────────────────────────────────────────────
function selectShopCategory(catId, btn) {
    shopActiveCat = catId;
    shopActiveSub = 'all';

    document.querySelectorAll('#shop-cat-pills .shop-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Reveal only the sub-category pills that belong to the selected category
    const subWrap = document.getElementById('shop-sub-pills');
    const subBtns = subWrap.querySelectorAll('.shop-pill.sub[data-parent]');
    let anyForCat = false;
    subBtns.forEach(b => {
        const show = catId !== 'all' && b.dataset.parent === catId;
        b.style.display = show ? '' : 'none';
        if (show) anyForCat = true;
    });
    subWrap.style.display = anyForCat ? 'flex' : 'none';

    subWrap.querySelectorAll('.shop-pill').forEach(b => b.classList.remove('active'));
    const allSubBtn = subWrap.querySelector('.shop-pill.sub[data-sub="all"]');
    if (allSubBtn) allSubBtn.classList.add('active');

    applyShopFilters();
}

// ── Sub-category selection ──────────────────────────────────────────────────
function selectShopSub(subId, btn) {
    shopActiveSub = subId;
    document.querySelectorAll('#shop-sub-pills .shop-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyShopFilters();
}

// ── Search (matches category, sub-category, and product name) ──────────────
function filterShop(query) {
    shopSearchQuery = query.trim().toLowerCase();
    applyShopFilters();
}

// ── Combined filter pass ────────────────────────────────────────────────────
function applyShopFilters() {
    document.querySelectorAll('.shop-item').forEach(item => {
        const matchCat    = shopActiveCat === 'all' || item.dataset.cat === shopActiveCat;
        const matchSub    = shopActiveSub === 'all' || item.dataset.sub === shopActiveSub;
        const matchSearch = shopSearchQuery === '' || (item.dataset.search || '').includes(shopSearchQuery);
        item.dataset.matched = (matchCat && matchSub && matchSearch) ? 'true' : 'false';
    });

    shopCurrentPage = 1;
    renderShopPage();
}

// ── Render the current page of the filtered result set ──────────────────────
function renderShopPage() {
    const items   = Array.from(document.querySelectorAll('.shop-item'));
    const matched = items.filter(item => item.dataset.matched === 'true');
    const totalPages = Math.max(1, Math.ceil(matched.length / SHOP_PAGE_SIZE));

    if (shopCurrentPage > totalPages) shopCurrentPage = totalPages;
    if (shopCurrentPage < 1) shopCurrentPage = 1;

    const start = (shopCurrentPage - 1) * SHOP_PAGE_SIZE;
    const end   = start + SHOP_PAGE_SIZE;

    items.forEach(item => { item.style.display = 'none'; });
    matched.forEach((item, idx) => {
        if (idx >= start && idx < end) item.style.display = '';
    });

    document.getElementById('no-results').style.display = matched.length === 0 ? 'block' : 'none';
    renderShopPagination(matched.length, totalPages);
}

// ── Pagination controls ──────────────────────────────────────────────────────
function renderShopPagination(totalItems, totalPages) {
    const wrap = document.getElementById('shop-pagination');

    if (totalPages <= 1) {
        wrap.innerHTML = '';
        return;
    }

    let html = `<button type="button" class="shop-page-btn" ${shopCurrentPage === 1 ? 'disabled' : ''} onclick="goToShopPage(${shopCurrentPage - 1})"><i class="ri-arrow-left-s-line"></i></button>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `<button type="button" class="shop-page-btn ${i === shopCurrentPage ? 'active' : ''}" onclick="goToShopPage(${i})">${i}</button>`;
    }

    html += `<button type="button" class="shop-page-btn" ${shopCurrentPage === totalPages ? 'disabled' : ''} onclick="goToShopPage(${shopCurrentPage + 1})"><i class="ri-arrow-right-s-line"></i></button>`;

    wrap.innerHTML = html;
}

function goToShopPage(page) {
    shopCurrentPage = page;
    renderShopPage();
    document.getElementById('shop-grid').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Wishlist ──
function toggleWishlist(btn, e) {
    e.preventDefault();
    e.stopPropagation();

    const id    = btn.dataset.id;
    const name  = btn.dataset.name;
    const price = btn.dataset.price;
    const image = btn.dataset.image;

    fetch('{{ route('wish-list.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id, name, price, image })
    })
    .then(r => r.json())
    .then(data => {
        const icon = btn.querySelector('i');
        if (data.inWishlist) {
            btn.classList.add('active');
            icon.classList.replace('ri-heart-line', 'ri-heart-fill');
        } else {
            btn.classList.remove('active');
            icon.classList.replace('ri-heart-fill', 'ri-heart-line');
        }
        // persist state in localStorage so it survives page reload
        const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
        if (data.inWishlist) saved[id] = true; else delete saved[id];
        localStorage.setItem('rv_wishlist', JSON.stringify(saved));

        if (typeof updateWishlistBadge === 'function') updateWishlistBadge(data.count);
    });
}

// Restore heart states from localStorage + set up initial pagination on load
document.addEventListener('DOMContentLoaded', () => {
    const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
    document.querySelectorAll('.wl-heart-btn').forEach(btn => {
        if (saved[btn.dataset.id]) {
            btn.classList.add('active');
            const icon = btn.querySelector('i');
            icon.classList.replace('ri-heart-line', 'ri-heart-fill');
        }
    });

    applyShopFilters();
});
</script>
@endsection
