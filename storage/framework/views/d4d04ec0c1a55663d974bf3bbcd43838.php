<?php $__env->startSection('css'); ?>
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
    }
    .gb-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }

    .gb-card-img-wrap { position: relative; overflow: hidden; height: 13rem; }
    .gb-card-img {
        width: 100%; height: 100%; object-fit: cover;
        display: block; transition: transform .5s;
    }
    .gb-card:hover .gb-card-img { transform: scale(1.08); }

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

    .gb-name {
        font-weight: 700; font-size: .95rem;
        color: #1f2937; margin-bottom: .2rem;
    }
    .gb-desc {
        font-size: .8rem; color: #6b7280; margin-bottom: .4rem;
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
        text-decoration: none !important;
    }
    .bts-gb-explore-btn:hover { opacity: .85; color: #fff; text-decoration: none !important; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
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
?>

<?php if(($section ?? 'gift-box') === 'gift-box'): ?>
<div class="cat-wrapper py-5">
<div class="container px-4">

    
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

    
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
        <button class="filter-btn active" onclick="filterBoxes('all', this)">All</button>
        <button class="filter-btn" onclick="filterBoxes('sanrio', this)">Sanrio Characters</button>
        <button class="filter-btn" onclick="filterBoxes('cartoon', this)">Cartoon Special</button>
    </div>

    
    <div class="row g-4" id="gb-grid" data-live-listing="<?php echo e($liveListing); ?>" data-live-signature="<?php echo e($liveSignature); ?>">
        <?php $__empty_1 = true; $__currentLoopData = $allDbProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $subName   = $product->_sub_name ?? $product->subCategory?->name ?? '';
            $tag       = $giftTagMap[$subName] ?? 'bts';
            $image     = $product->images->first()?->image_url ?? $fallbackImage;
            $cheapest  = $product->cheapestVariant();
            $priceLabel = $cheapest ? 'From PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
        ?>
        <div class="col-6 col-md-4 gb-item" data-tag="<?php echo e($tag); ?>" data-live-product="<?php echo e($product->id); ?>">
            <div class="gb-card">
                <div class="gb-card-img-wrap">
                    <img src="<?php echo e($image); ?>" alt="<?php echo e($product->name); ?>" class="gb-card-img" data-live-image>
                    <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['label' => $product->discountLabel(),'live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->discountLabel()),'live' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $attributes = $__attributesOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $component = $__componentOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__componentOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
                    <?php if($subName): ?>
                        <span class="gb-badge" style="background:#ec4899;"><?php echo e($subName); ?></span>
                    <?php endif; ?>
                    <button class="gb-heart-btn"
                            data-id="p-<?php echo e($product->id); ?>"
                            data-name="<?php echo e($product->name); ?>"
                            data-price="<?php echo e($priceLabel); ?>"
                            data-image="<?php echo e($image); ?>"
                            onclick="toggleWishlistGb(this)"
                            title="Add to Wishlist">
                        <i class="ri-heart-line"></i>
                    </button>
                </div>
                <div class="p-3 d-flex flex-column">
                    <h6 class="gb-name mb-1" data-live-name><?php echo e($product->name); ?></h6>
                    <p class="gb-desc"><?php echo e($product->description); ?></p>
                    <p class="gb-price" data-live-price data-live-prefix="From "><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $cheapest,'prefix' => 'From ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cheapest),'prefix' => 'From ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></p>
                    <?php if($product->variants->count() > 1): ?>
                        <p class="text-muted mb-2" style="font-size:.75rem;"><?php echo e($product->variants->count()); ?> variants available</p>
                    <?php endif; ?>
                    <a href="<?php echo e(route('product-item', $product->slug)); ?>" class="gb-add-btn" style="background:linear-gradient(135deg,#f472b6,#ec4899);text-decoration:none!important;">
                        View Product <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center py-5">
            <i class="ri-gift-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No gift boxes found. Please run the database seeder.</p>
        </div>
        <?php endif; ?>
    </div>

    <div id="gb-no-results" class="text-center py-5" style="display:none;">
        <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
        <p class="text-muted mt-2">No gift boxes found for this filter.</p>
    </div>

</div>
</div>
<?php endif; ?>

<?php if(($section ?? 'gift-box') === 'bts-gift-box'): ?>


<div class="bts-gb-wrapper">

    
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

        
        <div style="line-height:0;margin-top:3rem;margin-bottom:-2px;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;display:block;">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="#fff"/>
            </svg>
        </div>
    </div>

    <div class="container px-4 pb-5">

        
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-5">
            <button class="filter-btn active" onclick="filterBtsGiftBoxes('all', this)">All</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('era', this)">Era Boxes</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('bt21', this)">BT21 World</button>
            <button class="filter-btn" onclick="filterBtsGiftBoxes('ot7', this)">OT7 Special</button>
        </div>

        
        <div class="row g-4" id="bts-gb-grid" data-live-listing="<?php echo e($liveListing); ?>" data-live-signature="<?php echo e($liveSignature); ?>">
            <?php $__empty_1 = true; $__currentLoopData = $allDbProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $subName    = $product->_sub_name ?? $product->subCategory?->name ?? '';
                $tag        = $btsGiftTagMap[$subName] ?? 'era';
                $image      = $product->images->first()?->image_url ?? $fallbackImage;
                $cheapest   = $product->cheapestVariant();
                $priceLabel = $cheapest ? 'From PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
            ?>
            <div class="col-6 col-md-4 bts-gb-item" data-tag="<?php echo e($tag); ?>" data-live-product="<?php echo e($product->id); ?>">
                <div class="gb-card">
                    <div class="gb-card-img-wrap">
                        <img src="<?php echo e($image); ?>" alt="<?php echo e($product->name); ?>" class="gb-card-img" data-live-image>
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['label' => $product->discountLabel(),'live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->discountLabel()),'live' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $attributes = $__attributesOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $component = $__componentOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__componentOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
                        <?php if($subName): ?>
                            <span class="gb-badge" style="background:#7c3aed;"><?php echo e($subName); ?></span>
                        <?php endif; ?>
                        <button class="gb-heart-btn"
                                data-id="p-<?php echo e($product->id); ?>"
                                data-name="<?php echo e($product->name); ?>"
                                data-price="<?php echo e($priceLabel); ?>"
                                data-image="<?php echo e($image); ?>"
                                onclick="toggleWishlistGb(this)"
                                title="Add to Wishlist">
                            <i class="ri-heart-line"></i>
                        </button>
                    </div>
                    <div class="p-3 d-flex flex-column">
                        <h6 class="gb-name mb-1" data-live-name><?php echo e($product->name); ?></h6>
                        <p class="gb-desc"><?php echo e($product->description); ?></p>
                        <p class="bts-gb-price" data-live-price data-live-prefix="From "><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $cheapest,'prefix' => 'From ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cheapest),'prefix' => 'From ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></p>
                        <?php if($product->variants->count() > 1): ?>
                            <p class="text-muted mb-2" style="font-size:.75rem;"><?php echo e($product->variants->count()); ?> variants available</p>
                        <?php endif; ?>
                        <a href="<?php echo e(route('product-item', $product->slug)); ?>" class="bts-gb-explore-btn">
                            View Product <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12 text-center py-5">
                <i class="ri-gift-line" style="font-size:2.5rem;color:#d1d5db;"></i>
                <p class="text-muted mt-2">No BTS gift boxes found. Please run the database seeder.</p>
            </div>
            <?php endif; ?>
        </div>

        <div id="bts-gb-no-results" class="text-center py-5" style="display:none;">
            <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No BTS gift boxes found for this filter.</p>
        </div>

    </div>
</div>
<?php endif; ?>

<?php if(($section ?? 'gift-box') === 'ceramic'): ?>


<div id="ceramic-collection" style="background:linear-gradient(to bottom,#fdf8f0,#f5ede0);padding:4rem 0 3rem;">
<div class="container px-4">

    
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3rem);color:#5c3d1e;">
            Ceramic Collection
        </h2>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1rem;">
            Handpicked, aesthetic ceramic pieces to brighten up your home and daily routine.
        </p>
    </div>

    
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

    
    <div class="row g-4" id="cer-grid" data-live-listing="<?php echo e($liveListing); ?>" data-live-signature="<?php echo e($liveSignature); ?>">
        <?php $__empty_1 = true; $__currentLoopData = $allDbProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $subName    = $product->_sub_name ?? $product->subCategory?->name ?? '';
            $tag        = $ceramicTagMap[$subName] ?? 'mug';
            $image      = $product->images->first()?->image_url ?? $fallbackImage;
            $cheapest   = $product->cheapestVariant();
            $priceLabel = $cheapest ? 'PKR ' . number_format($cheapest->effectivePrice()) : 'PKR —';
            $variantName = $product->variants->first()?->variant_name;
        ?>
        <div class="col-6 col-md-4 cer-item" data-tag="<?php echo e($tag); ?>" data-live-product="<?php echo e($product->id); ?>">
            <div class="gb-card">
                <div class="gb-card-img-wrap">
                    <img src="<?php echo e($image); ?>" alt="<?php echo e($product->name); ?>" class="gb-card-img" data-live-image>
                    <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['label' => $product->discountLabel(),'live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->discountLabel()),'live' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $attributes = $__attributesOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__attributesOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc259565ab925afbe6d933017bcb8ab06)): ?>
<?php $component = $__componentOriginalc259565ab925afbe6d933017bcb8ab06; ?>
<?php unset($__componentOriginalc259565ab925afbe6d933017bcb8ab06); ?>
<?php endif; ?>
                    <?php if($subName): ?>
                        <span class="gb-badge" style="background:#C8A98A;"><?php echo e($subName); ?></span>
                    <?php endif; ?>
                    <button class="gb-heart-btn"
                            data-id="p-<?php echo e($product->id); ?>"
                            data-name="<?php echo e($product->name); ?>"
                            data-price="<?php echo e($priceLabel); ?>"
                            data-image="<?php echo e($image); ?>"
                            onclick="toggleWishlistGb(this)"
                            title="Add to Wishlist">
                        <i class="ri-heart-line"></i>
                    </button>
                </div>
                <div class="p-3 d-flex flex-column">
                    <h6 class="gb-name mb-1" data-live-name><?php echo e($product->name); ?></h6>
                    <p class="gb-desc"><?php echo e($product->description); ?></p>
                    <p class="gb-price" data-live-price><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $cheapest]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cheapest)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></p>
                    <?php if($variantName): ?>
                        <p class="text-muted mb-2" style="font-size:.75rem;"><?php echo e($variantName); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo e(route('product-item', $product->slug)); ?>" class="gb-add-btn" style="background:linear-gradient(135deg,#C8A98A,#8B5E3C);text-decoration:none!important;">
                        View Product <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center py-5">
            <i class="ri-cup-line" style="font-size:2.5rem;color:#d1d5db;"></i>
            <p class="text-muted mt-2">No ceramic items found. Please run the database seeder.</p>
        </div>
        <?php endif; ?>
    </div>

    <div id="cer-no-results" class="text-center py-5" style="display:none;">
        <i class="ri-search-line" style="font-size:2.5rem;color:#d1d5db;"></i>
        <p class="text-muted mt-2">No ceramic items found for this filter.</p>
    </div>

</div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
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
}

// ── Wishlist ─────────────────────────────────────────────────────────────
function toggleWishlistGb(btn) {
    const id    = btn.dataset.id;
    const name  = btn.dataset.name;
    const price = btn.dataset.price;
    const image = btn.dataset.image;

    fetch('<?php echo e(route('wish-list.toggle')); ?>', {
        method: 'POST',
        headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>' },
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
    window.location.href = '<?php echo e(url('/cart')); ?>';
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

    const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
    document.querySelectorAll('.gb-heart-btn').forEach(btn => {
        if (saved[btn.dataset.id]) {
            btn.classList.add('active');
            btn.querySelector('i').classList.replace('ri-heart-line','ri-heart-fill');
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/category.blade.php ENDPATH**/ ?>