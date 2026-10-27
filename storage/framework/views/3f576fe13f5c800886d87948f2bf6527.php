

<?php
    // ── Determine product type ──────────────────────────────────────────────
    // Priority: PHP $type var (from route) → query param
    $productType = $type ?? request('type', 'mystery-balls');

    // BTS params passed from bts-merch page
    $bias = request('bias');
    $era  = request('era');

    // Resolve BTS sub-type: if bias is set, it could be scoop or balls
    $btsSubType = request('bts_sub', 'scoop'); // 'scoop' or 'balls'

    // Normalise: bts-mystery-scoop / bts-mystery-balls come from bts-merch goToProduct()
    if ($productType === 'mystery-scoops' && $bias) $productType = 'bts-scoop';
    if ($productType === 'mystery-balls'  && $bias) $productType = 'bts-balls';

    // ── Theme config per type ───────────────────────────────────────────────
    $themes = [
        'mystery-balls' => [
            'bg'         => 'linear-gradient(to bottom,#fdf2f8,#f5f3ff)',
            'accent'     => '#f472b6',
            'accent2'    => '#fb7185',
            'btnGrad'    => 'linear-gradient(135deg,#f472b6,#fb7185)',
            'stepBorder' => '#fbcfe8',
            'badgeBg'    => '#f472b6',
        ],
        'mystery-scoops' => [
            'bg'         => 'linear-gradient(to bottom,#fdf2f8,#f5f3ff)',
            'accent'     => '#f472b6',
            'accent2'    => '#fb7185',
            'btnGrad'    => 'linear-gradient(135deg,#f472b6,#fb7185)',
            'stepBorder' => '#fbcfe8',
            'badgeBg'    => '#f472b6',
        ],
        'bts-scoop' => [
            'bg'         => 'linear-gradient(to bottom,#ede9fe,#fdf2f8)',
            'accent'     => '#7c3aed',
            'accent2'    => '#a855f7',
            'btnGrad'    => 'linear-gradient(135deg,#7c3aed,#a855f7)',
            'stepBorder' => '#ddd6fe',
            'badgeBg'    => '#7c3aed',
        ],
        'bts-balls' => [
            'bg'         => 'linear-gradient(to bottom,#ede9fe,#fdf2f8)',
            'accent'     => '#7c3aed',
            'accent2'    => '#a855f7',
            'btnGrad'    => 'linear-gradient(135deg,#7c3aed,#a855f7)',
            'stepBorder' => '#ddd6fe',
            'badgeBg'    => '#7c3aed',
        ],
        'ceramic' => [
            'bg'         => 'linear-gradient(to bottom,#fdf8f0,#f5ede0)',
            'accent'     => '#8B5E3C',
            'accent2'    => '#C8A98A',
            'btnGrad'    => 'linear-gradient(135deg,#C8A98A,#8B5E3C)',
            'stepBorder' => '#e8d5c0',
            'badgeBg'    => '#C8A98A',
        ],
        'gift-box' => [
            'bg'         => 'linear-gradient(to bottom,#fdf2f8,#f5f3ff)',
            'accent'     => '#fb7185',
            'accent2'    => '#f472b6',
            'btnGrad'    => 'linear-gradient(135deg,#fb7185,#f472b6)',
            'stepBorder' => '#fbcfe8',
            'badgeBg'    => '#fb7185',
        ],
    ];
    $theme = $themes[$productType] ?? $themes['mystery-balls'];

    // ── BTS member labels ───────────────────────────────────────────────────
    $memberLabels = [
        'jungkook'=>'Jungkook 🐰','taehyung'=>'Taehyung 🐯','jimin'=>'Jimin 🐥',
        'namjoon'=>'Namjoon 🐨','jin'=>'Jin 🐹','jhope'=>'J-Hope 🐿️',
        'suga'=>'Suga 🐱','ot7'=>'OT7 💜','bt21'=>'BT21 ✨',
    ];
    $biasLabel = $bias ? ($memberLabels[$bias] ?? ucfirst($bias)) : null;
    $btsLabel  = $biasLabel . ($era ? ' · '.$era : '');

    // ── Reviews per product type (no duplicates, contextually relevant) ──────
    $allReviews = [
        'mystery-balls' => [
            ['name'=>'Sara K.',    'date'=>'12 March, 2026',    'stars'=>5, 'text'=>'Got 10 balls and every single one was a surprise! The quality is amazing and the packaging was so cute.'],
            ['name'=>'Hina R.',    'date'=>'28 February, 2026', 'stars'=>4, 'text'=>'Really fun to unbox! A couple of items were similar but overall great value for PKR 1,000 per 5.'],
            ['name'=>'Zara M.',    'date'=>'5 January, 2026',   'stars'=>5, 'text'=>'Ordered 15 balls as gifts — everyone loved them. Will definitely order again!'],
        ],
        'mystery-scoops' => [
            ['name'=>'Ayesha T.',  'date'=>'18 April, 2026',    'stars'=>5, 'text'=>'The Premium scoop was absolutely worth it. So many cute items, nothing repeated!'],
            ['name'=>'Maryam N.', 'date'=>'24 September, 2026', 'stars'=>4, 'text'=>"Everything looks amazing, I'll make sure to order here every time!"],
            ['name'=>'Fatima B.', 'date'=>'10 March, 2026',     'stars'=>5, 'text'=>'Ordered the Classic scoop and was blown away. Great curation, fast delivery.'],
        ],
        'bts-scoop' => [
            ['name'=>'Army4ever',  'date'=>'2 May, 2026',       'stars'=>5, 'text'=>'The Jungkook themed scoop had the most adorable items 💜 Totally worth every rupee!'],
            ['name'=>'PurpleYou', 'date'=>'15 April, 2026',     'stars'=>5, 'text'=>'OT7 scoop was perfect — items from all members. Borahae! 💜'],
            ['name'=>'Sana A.',   'date'=>'1 March, 2026',      'stars'=>4, 'text'=>'BTS themed items are so unique. Loved the packaging and the surprise element!'],
        ],
        'bts-balls' => [
            ['name'=>'Noor F.',    'date'=>'20 April, 2026',    'stars'=>5, 'text'=>'Ordered 20 BTS balls for my ARMY friends — they were obsessed! Great quality.'],
            ['name'=>'Rimsha K.', 'date'=>'8 March, 2026',      'stars'=>4, 'text'=>'Every ball had something different. The BTS theme really comes through in the items 💜'],
            ['name'=>'Laiba S.',  'date'=>'14 February, 2026',  'stars'=>5, 'text'=>'Perfect gift for any ARMY. Minimum 5 is very reasonable. Will order more!'],
        ],
        'ceramic' => [
            ['name'=>'Iqra M.',    'date'=>'30 March, 2026',    'stars'=>5, 'text'=>'The ceramic mug is gorgeous! High quality and the design is exactly as shown.'],
            ['name'=>'Dua H.',    'date'=>'11 February, 2026',  'stars'=>4, 'text'=>'Pre-order was smooth, arrived in 2 weeks. The mug is sturdy and so aesthetic!'],
            ['name'=>'Mahnoor A.','date'=>'5 January, 2026',    'stars'=>5, 'text'=>'Bought 2 mugs as gifts. Everyone loved them. The ceramic quality is top notch.'],
        ],
        'gift-box' => [
            ['name'=>'Alina R.',   'date'=>'22 April, 2026',    'stars'=>5, 'text'=>"Tata's Space gift box was the cutest thing ever! Perfect for any BTS fan 💜"],
            ['name'=>'Bisma T.',  'date'=>'3 March, 2026',      'stars'=>5, 'text'=>'Gifted this to my friend and she cried happy tears. The packaging is beautiful!'],
            ['name'=>'Hira N.',   'date'=>'19 January, 2026',   'stars'=>4, 'text'=>'Great value, lots of cute items inside. Would love more variety in future boxes.'],
        ],
    ];
    // Fall back to mystery-scoops reviews if type not found
    $reviews = $allReviews[$productType] ?? $allReviews['mystery-scoops'];

    // ── DB product behind the generic (non product-item) mystery pages ──────
    // HomeController::genericProductPage resolves it (the BTS member's product
    // for ?bias=). Prices shown here and the ids addToCart() sends to the cart
    // API all come from it; ceramic/gift-box pages have none.
    $genericProduct = $genericProduct ?? null;
    $dbProductMap   = ['product_id' => $genericProduct?->id];

    // Mystery Balls quantity options — see Product::packOptions()
    $ballPacks = $genericProduct?->packOptions() ?? [];
    $ballsMode = $genericProduct?->pricing_mode;
    $ballsMin  = $ballPacks[0]['qty'] ?? 5;
    $ballsMax  = $ballPacks ? $ballPacks[count($ballPacks) - 1]['qty'] : 50;

    // Mystery Scoop sizes, cheapest first ("Classic Scoop" → "Classic")
    $scoopOptions = $genericProduct && ! $genericProduct->usesPackPricing()
        ? $genericProduct->variants->sortBy('price')->values()->map(fn ($v) => [
            'id'       => $v->id,
            'name'     => trim(preg_replace('/\bscoop\b/i', '', $v->variant_name)) ?: $v->variant_name,
            'price'    => $v->effectivePrice(),
            'original' => $v->hasDiscount() ? (float) $v->price : null,
            'percent'  => $v->discountPercent(),
        ])->all()
        : [];
    $firstScoop = $scoopOptions[0] ?? null;

    // Live updates (layouts/live-updates): the DB product this page shows, and a
    // fingerprint of its sizes / packs so a structural admin change re-renders the page
    $livePageProduct   = ($type ?? null) === 'product-item' ? ($product ?? null) : $genericProduct;
    $livePageStructure = $livePageProduct ? app(\App\Services\LiveUpdateService::class)->structure($livePageProduct) : null;
?>

<?php $__env->startSection('css'); ?>
<style>
    .pd-wrapper { background: <?php echo e($theme['bg']); ?>; min-height: 100vh; }

    .product-card {
        border: 0; border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        overflow: hidden; transition: box-shadow .3s;
    }
    .product-card:hover { box-shadow: 0 12px 32px rgba(0,0,0,.13); }
    /* contain: always show the whole photo, even when the details column makes the card tall */
    .product-img { position: relative; width: 100%; height: 100%; min-height: 18rem; object-fit: contain; display: block; }
    /* the space around a fitted photo is filled with a soft blurred copy of it (set up in the page script) */
    .product-img-frame { overflow: hidden; background: #fff; }
    .product-img-frame::before {
        content: ''; position: absolute; inset: 0;
        background: var(--product-img) center / cover no-repeat;
        filter: blur(28px); transform: scale(1.2); opacity: .45;
    }

    /* Size selector (scoops) */
    .size-btn {
        border: 2px solid #e5e7eb; border-radius: .5rem;
        padding: .5rem 1rem; background: #fff; cursor: pointer;
        transition: border-color .2s, background .2s; text-align: center; min-width: 5.5rem;
    }
    .size-btn:hover { border-color: <?php echo e($theme['accent']); ?>; }
    .size-btn.active { border-color: <?php echo e($theme['accent']); ?>; background: #fdf2f8; }
    .size-btn .size-name { font-weight: 600; font-size: .85rem; color: #1f2937; }
    .size-btn.active .size-name { color: <?php echo e($theme['accent']); ?>; }
    .size-btn .size-price { font-size: .75rem; color: #6b7280; }
    .size-btn.balls-pack-btn { min-width: 4.75rem; padding: .35rem .6rem; }

    /* Quantity */
    .qty-btn {
        width: 2.5rem; height: 2.5rem; border-radius: 50%;
        border: 2px solid #e5e7eb; background: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; cursor: pointer; transition: border-color .2s, background .2s;
    }
    .qty-btn:hover:not(:disabled) { border-color: <?php echo e($theme['accent']); ?>; background: #f5f3ff; }
    .qty-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* Info notes */
    .info-note {
        background: #fefce8; border: 1px solid #fde68a;
        border-radius: .5rem; padding: .75rem 1rem;
        font-size: .8rem; color: #92400e;
    }
    .info-alert {
        background: linear-gradient(to right,#eff6ff,#eef2ff);
        border: 1px solid #bfdbfe; border-radius: .75rem;
        padding: 1rem 1.25rem; color: #1e3a8a; font-size: .875rem;
    }
    .info-preorder {
        background: #fffbeb; border: 1px solid #fcd34d;
        border-radius: .75rem; padding: 1rem 1.25rem; font-size: .875rem;
    }
    /* Summary */
    .summary-card {
        border: 0; border-radius: 1rem;
        box-shadow: 0 8px 28px rgba(0,0,0,.10);
        background: rgba(255,255,255,.9);
        backdrop-filter: blur(8px);
    }

    /* How it works */
    .how-step { border-left: 3px solid <?php echo e($theme['stepBorder']); ?>; padding-left: 1rem; margin-bottom: 1.5rem; }
    .how-step:last-child { margin-bottom: 0; }

    /* Reviews */
    .review-item { border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; margin-bottom: 1rem; }
    .review-item:last-child { border-bottom: 0; margin-bottom: 0; }
    .star { color: #facc15; }
    .review-img { width: 4.5rem; height: 4.5rem; object-fit: cover; border-radius: .5rem; }

    /* Ceramic specs */
    .spec-box {
        background: #f9f5f0; border: 1px solid #e8d5c0;
        border-radius: .5rem; padding: .75rem 1rem;
    }
    .spec-label { font-size: .7rem; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: .05em; }
    .spec-value { font-size: .9rem; font-weight: 600; color: #1f2937; }

    /* Category/collection badge — colored inline per product theme */
    .bts-info-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        border: 1px solid; border-radius: 2rem; padding: .3rem .9rem;
        font-size: .82rem; font-weight: 600;
        margin-bottom: 1rem;
    }

    /* Related products */
    .related-prod-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 4px 16px rgba(0,0,0,.08);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        height: 100%;
    }
    .related-prod-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }
    .related-prod-img-wrap { position: relative; height: 9rem; overflow: hidden; }
    .related-prod-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .related-prod-name { font-weight: 700; font-size: .85rem; color: #1f2937; margin-bottom: .3rem; }
    .related-prod-price { font-weight: 700; font-size: .82rem; margin-bottom: .75rem; }
    .related-explore-btn {
        border: 0; color: #fff; font-weight: 600; font-size: .8rem;
        border-radius: .5rem; padding: .5rem 1rem;
        transition: opacity .2s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: .3rem;
        text-decoration: none !important;
    }
    .related-explore-btn:hover { opacity: .85; color: #fff; text-decoration: none !important; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="pd-wrapper py-5">
<div class="container px-4">


<?php if($productType === 'mystery-balls'): ?>
<?php $__env->startSection('title', 'Mystery Balls'); ?>

    <div class="text-center mb-5">
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($theme['accent']); ?>,<?php echo e($theme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Mystery Balls
        </h1>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1.05rem;">
            Unwrap the fun! Each mystery ball contains a surprise item identical to our famous scoops.
        </p>
    </div>

    <div class="info-alert mb-4 d-flex gap-2 align-items-start">
        <i class="ri-information-line mt-1" style="color:#3b82f6;flex-shrink:0;"></i>
        <div><strong>Quantity Rules:</strong> Minimum order is <?php echo e($ballsMin); ?> balls. You can order up to <?php echo e($ballsMax); ?> balls per batch. Orders must be in multiples of 5 (e.g., 5, 10, 15...).</div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="https://images.unsplash.com/photo-1615485925694-a031e97d8964?w=600&q=80" alt="Mystery Balls" class="product-img h-100">
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['id' => 'discount-badge','label' => isset($ballPacks[0]['percent']) ? '-' . $ballPacks[0]['percent'] . '%' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'discount-badge','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(isset($ballPacks[0]['percent']) ? '-' . $ballPacks[0]['percent'] . '%' : null)]); ?>
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
                        <span class="position-absolute top-0 start-0 m-3 badge" style="background:<?php echo e($theme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;">Best Seller</span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-center h-100">
                            <h3 class="fw-bold mb-3">Standard Mystery Balls</h3>
                            <p class="text-muted mb-4">Get the thrill of the unknown! Our mystery balls are packed with the same high-quality stationery, accessories, and cute items found in our scoops.</p>
                            <div class="mb-4">
                                <?php echo $__env->make('pages.partials.balls-pricing', ['packs' => $ballPacks, 'mode' => $ballsMode, 'accent' => $theme['accent']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                            <div class="bg-light rounded-3 p-4 text-center">
                                <div class="fw-medium text-muted small mb-3">Select Quantity (Multiples of 5)</div>
                                <div class="d-flex align-items-center justify-content-center gap-4">
                                    <button class="qty-btn" id="qty-minus" onclick="updateQtyBalls(-5)"><i class="ri-subtract-line"></i></button>
                                    <div>
                                        <div class="fw-bold" id="qty-display" style="font-size:2.5rem;line-height:1;"><?php echo e($ballsMin); ?></div>
                                        <div class="text-muted" style="font-size:.75rem;">Balls</div>
                                    </div>
                                    <button class="qty-btn" id="qty-plus" onclick="updateQtyBalls(5)"><i class="ri-add-line"></i></button>
                                </div>
                                <div class="text-muted mt-2" style="font-size:.8rem;">Range: <?php echo e($ballsMin); ?> - <?php echo e($ballsMax); ?> balls</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">🎁 How Mystery Balls Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Pack Size</div>
                    <p class="text-muted small mb-0">Select how many Mystery Balls you'd like. You can choose any quantity from 5 to 50 balls in increments of 5.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose the number of packs you'd like to order. You can mix different Mystery Ball collections in the same order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Use the comment box before adding to cart to let us know:</p>
                    <ul class="text-muted small mb-1 ps-3">
                        <li>Preferred themes or characters</li>
                        <li>Items you'd love to receive</li>
                        <li>Items you'd like to avoid</li>
                        <li>Any replacement suggestions</li>
                    </ul>
                    <p class="text-muted small mb-0">We'll do our best to personalize your mystery experience.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate Your Mystery</div>
                    <p class="text-muted small mb-1">Every Mystery Ball is packed with care to keep every order exciting and unique.</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If your order contains too many identical items, we'll replace some with similar items to keep your mystery fun and varied.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Enjoy the Surprise</div>
                    <p class="text-muted small mb-0">Your Mystery Balls arrive beautifully packed and ready to open. Enjoy discovering what's inside and share the excitement! 🎉</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review['name']); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review['date']); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review['stars']?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-0"><?php echo e($review['text']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="summary-qty"><?php echo e($ballsMin); ?> Balls</span>
                </div>
                <?php if($ballsMode === \App\Models\Product::PRICING_AUTOMATIC && $ballPacks): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Price per 5</span>
                    <span class="fw-semibold balls-base-price"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $ballPacks[0]['price'] * 5 / $ballsMin,'original' => $ballPacks[0]['original'] !== null ? $ballPacks[0]['original'] * 5 / $ballsMin : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['price'] * 5 / $ballsMin),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['original'] !== null ? $ballPacks[0]['original'] * 5 / $ballsMin : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-4 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="summary-total" style="color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $ballPacks[0]['price'] ?? null,'original' => $ballPacks[0]['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4">
                    <label class="fw-medium small mb-2 d-block">Special Requests (Optional)</label>
                    <textarea class="form-control" rows="4" id="mb-special-requests" placeholder="E.g., color preferences, specific themes..." style="border-radius:.5rem;font-size:.875rem;resize:none;border:1px solid #e5e7eb;"></textarea>
                </div>
                <button onclick="addToCart('mystery-balls')" id="balls-add-btn" <?php if(! $ballPacks || ! $ballPacks[0]['available']): echo 'disabled'; endif; ?> class="btn text-white w-100 py-2 fw-semibold" style="background:<?php echo e($theme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;">
                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart — <span id="btn-total">PKR <?php echo e(number_format($ballPacks[0]['price'] ?? 0)); ?></span>
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>


<?php elseif($productType === 'mystery-scoops'): ?>
<?php $__env->startSection('title', 'Mystery Scoops'); ?>

    <div class="text-center mb-5">
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($theme['accent']); ?>,<?php echo e($theme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Mystery Scoops
        </h1>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1.05rem;">
            Choose your scoop size and let the mystery begin! Each scoop is carefully curated with amazing items.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=600&q=80" alt="Mystery Scoop" class="product-img h-100">
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['id' => 'discount-badge','label' => isset($firstScoop['percent']) ? '-' . $firstScoop['percent'] . '%' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'discount-badge','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(isset($firstScoop['percent']) ? '-' . $firstScoop['percent'] . '%' : null)]); ?>
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
                        <span class="position-absolute top-0 start-0 m-3 badge" style="background:<?php echo e($theme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;">Best Seller</span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-start h-100">
                            <h3 class="fw-bold mb-2">Classic Mystery Scoop</h3>
                            <p class="text-muted small mb-3">A delightful surprise of curated items in our Classic size. Each scoop contains a variety of beads, charms, and accessories.</p>
                            <div class="info-note mb-3">⚠️ Note: More than 4 same beads will be upgraded to prevent same items. If you don't want upgrades, please specify in the comments.</div>
                            <div class="mb-3">
                                <span class="fw-bold" id="display-price" style="font-size:1.5rem;color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                                <span class="text-muted small ms-1" id="display-size-label">per <?php echo e(strtolower($firstScoop['name'] ?? 'scoop')); ?></span>
                            </div>
                            <div class="mb-3">
                                <div class="fw-medium small mb-2">Select size</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php $__empty_1 = true; $__currentLoopData = $scoopOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $scoop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <button class="size-btn <?php echo e($i === 0 ? 'active' : ''); ?>" onclick="selectSize(this, <?php echo e($i); ?>)">
                                        <div class="size-name"><?php echo e($scoop['name']); ?></div><div class="size-price" data-scoop-idx="<?php echo e($i); ?>"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $scoop['price'],'original' => $scoop['original']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['price']),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['original'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></div>
                                    </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-muted small">This product is currently unavailable.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="fw-medium small mb-2">Quantity <span class="text-muted">(Min: 1, Max: 20)</span></div>
                                <div class="d-flex align-items-center gap-3">
                                    <button class="qty-btn" id="scoop-minus" onclick="updateQtyScoop(-1)" disabled><i class="ri-subtract-line"></i></button>
                                    <span class="fw-bold fs-5" id="scoop-qty-display">1</span>
                                    <button class="qty-btn" id="scoop-plus" onclick="updateQtyScoop(1)"><i class="ri-add-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">🎁 How Mystery Scoops Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Scoop Size</div>
                    <p class="text-muted small mb-1">Select your Mystery Scoop size:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Classic Scoop</li><li>Standard Scoop</li><li>Premium Scoop</li></ul>
                    <p class="text-muted small mb-0">Each size comes with a different value and surprise level.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose how many scoops you want (minimum 1, maximum 20 per size). You can also mix different sizes with different quantities in one order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Before adding to cart, use the comment box to tell us:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll try our best to personalize your mystery.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate the Surprise</div>
                    <p class="text-muted small mb-1">Our team handpicks each scoop with care. No two Mystery Scoops are the same — that's the magic!</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If more than 4 identical items are selected, they'll be upgraded automatically to keep your mystery exciting.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your Mystery Scoops beautifully packed and ready to surprise you. Open, explore, and enjoy the thrill of the unknown 🎉</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review['name']); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review['date']); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review['stars']?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-0"><?php echo e($review['text']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Size</span>
                    <span class="fw-semibold" id="summary-size"><?php echo e($firstScoop['name'] ?? '—'); ?></span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="summary-qty">1</span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Price per scoop</span>
                    <span class="fw-semibold" id="summary-unit-price"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="summary-total" style="color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4">
                    <label class="fw-medium small mb-2 d-block">Special Requests (Optional)</label>
                    <textarea class="form-control" rows="4" id="ms-special-requests" placeholder="E.g., preferred themes, items to avoid..." style="border-radius:.5rem;font-size:.875rem;resize:none;border:1px solid #e5e7eb;"></textarea>
                </div>
                <button onclick="addToCart('mystery-scoops')" <?php if(! $scoopOptions): echo 'disabled'; endif; ?> class="btn text-white w-100 py-2 fw-semibold" style="background:<?php echo e($theme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;">
                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart — <span id="btn-total">PKR <?php echo e(number_format($firstScoop['price'] ?? 0)); ?></span>
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>



<?php elseif($productType === 'bts-scoop'): ?>

<?php $__env->startSection('title', $bias && ($genericProduct ?? null) ? $genericProduct->name : 'BTS Mystery Scoop'); ?>

    <div class="text-center mb-5">
        <?php if($biasLabel): ?>
            <div class="bts-info-badge" style="background:color-mix(in srgb, <?php echo e($theme['accent']); ?> 12%, white);border-color:color-mix(in srgb, <?php echo e($theme['accent']); ?> 40%, white);color:<?php echo e($theme['accent']); ?>;"><i class="ri-heart-fill"></i> <?php echo e($btsLabel); ?></div>
        <?php endif; ?>
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($theme['accent']); ?>,<?php echo e($theme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            BTS Mystery Scoop 💜
        </h1>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1.05rem;">
            Exclusive ARMY-themed mystery scoop<?php echo e($biasLabel ? ' for '.$biasLabel : ''); ?>. Choose your size and let the magic begin!
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=600&q=80" alt="BTS Mystery Scoop" class="product-img h-100">
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['id' => 'discount-badge','label' => isset($firstScoop['percent']) ? '-' . $firstScoop['percent'] . '%' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'discount-badge','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(isset($firstScoop['percent']) ? '-' . $firstScoop['percent'] . '%' : null)]); ?>
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
                        <span class="position-absolute top-0 start-0 m-3 badge" style="background:<?php echo e($theme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;">ARMY Special</span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-start h-100">
                            <h3 class="fw-bold mb-2"><?php echo e($biasLabel ? $btsLabel.' Mystery Scoop' : 'BTS Mystery Scoop'); ?></h3>
                            <p class="text-muted small mb-3">Exclusive themed mystery items for ARMY! Each scoop is curated with BTS-themed goodies<?php echo e($biasLabel ? ' for '.$biasLabel : ''); ?>.</p>
                            <div class="info-note mb-3">⚠️ Note: More than 4 same items will be upgraded to prevent duplicates. Specify in comments if you prefer otherwise.</div>
                            <div class="mb-3">
                                <span class="fw-bold" id="display-price" style="font-size:1.5rem;color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                                <span class="text-muted small ms-1" id="display-size-label">per <?php echo e(strtolower($firstScoop['name'] ?? 'scoop')); ?></span>
                            </div>
                            <div class="mb-3">
                                <div class="fw-medium small mb-2">Select size</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php $__empty_1 = true; $__currentLoopData = $scoopOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $scoop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <button class="size-btn <?php echo e($i === 0 ? 'active' : ''); ?>" onclick="selectSize(this, <?php echo e($i); ?>)">
                                        <div class="size-name"><?php echo e($scoop['name']); ?></div><div class="size-price" data-scoop-idx="<?php echo e($i); ?>"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $scoop['price'],'original' => $scoop['original']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['price']),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['original'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></div>
                                    </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <span class="text-muted small">This product is currently unavailable.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="fw-medium small mb-2">Quantity <span class="text-muted">(Min: 1, Max: 20)</span></div>
                                <div class="d-flex align-items-center gap-3">
                                    <button class="qty-btn" id="scoop-minus" onclick="updateQtyScoop(-1)" disabled><i class="ri-subtract-line"></i></button>
                                    <span class="fw-bold fs-5" id="scoop-qty-display">1</span>
                                    <button class="qty-btn" id="scoop-plus" onclick="updateQtyScoop(1)"><i class="ri-add-line"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">💜 How BTS Mystery Scoops Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">💜 Step 1: Your Bias Selection</div>
                    <p class="text-muted small mb-0">You selected <strong><?php echo e($biasLabel ?? 'BTS'); ?></strong><?php echo e($era ? ' · '.$era : ''); ?>. Your scoop will be themed around your bias with exclusive member-specific items.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 2: Choose Your Scoop Size</div>
                    <p class="text-muted small mb-1">Select your Mystery Scoop size:</p>
                    <ul class="text-muted small mb-1 ps-3"><?php $__currentLoopData = $scoopOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scoop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($scoop['name']); ?> Scoop — <span data-scoop-idx="<?php echo e($loop->index); ?>"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $scoop['price'],'original' => $scoop['original']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['price']),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($scoop['original'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
                    <p class="text-muted small mb-0">Each size comes with a different value and surprise level.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 3: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose how many scoops you want (minimum 1, maximum 20 per size). You can also mix different sizes with different quantities in one order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 4: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Before adding to cart, use the comment box to tell us:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred member items or era themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll try our best to personalize your mystery.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 5: We Curate the ARMY Surprise</div>
                    <p class="text-muted small mb-1">Our team handpicks each scoop with BTS-themed care. Borahae! 💜</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If more than 4 identical items are selected, they'll be upgraded automatically to keep your mystery exciting.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 6: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your BTS Mystery Scoop beautifully packed and ready to surprise you. Open, explore, and enjoy the thrill of the unknown 🎉</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review['name']); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review['date']); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review['stars']?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-0"><?php echo e($review['text']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <?php if($biasLabel): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Bias</span>
                    <span class="fw-semibold" style="color:<?php echo e($theme['accent']); ?>;"><?php echo e($btsLabel); ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Size</span>
                    <span class="fw-semibold" id="summary-size"><?php echo e($firstScoop['name'] ?? '—'); ?></span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="summary-qty">1</span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Price per scoop</span>
                    <span class="fw-semibold" id="summary-unit-price"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="summary-total" style="color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $firstScoop['price'] ?? null,'original' => $firstScoop['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstScoop['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4">
                    <label class="fw-medium small mb-2 d-block">Special Requests (Optional)</label>
                    <textarea class="form-control" rows="4" id="bs-special-requests" placeholder="E.g., preferred member items, era themes..." style="border-radius:.5rem;font-size:.875rem;resize:none;border:1px solid #e5e7eb;"></textarea>
                </div>
                <button onclick="addToCart('bts-scoop')" <?php if(! $scoopOptions): echo 'disabled'; endif; ?> class="btn text-white w-100 py-2 fw-semibold" style="background:<?php echo e($theme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;">
                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart — <span id="btn-total">PKR <?php echo e(number_format($firstScoop['price'] ?? 0)); ?></span>
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>



<?php elseif($productType === 'bts-balls'): ?>
<?php $__env->startSection('title', $bias && ($genericProduct ?? null) ? $genericProduct->name : 'BTS Mystery Balls'); ?>

    <div class="text-center mb-5">
        <?php if($biasLabel): ?>
            <div class="bts-info-badge" style="background:color-mix(in srgb, <?php echo e($theme['accent']); ?> 12%, white);border-color:color-mix(in srgb, <?php echo e($theme['accent']); ?> 40%, white);color:<?php echo e($theme['accent']); ?>;"><i class="ri-heart-fill"></i> <?php echo e($btsLabel); ?></div>
        <?php endif; ?>
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($theme['accent']); ?>,<?php echo e($theme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            BTS Mystery Balls 💜
        </h1>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1.05rem;">
            Surprise mystery balls with ARMY-themed goodies<?php echo e($biasLabel ? ' for '.$biasLabel : ''); ?>!
        </p>
    </div>

    <div class="info-alert mb-4 d-flex gap-2 align-items-start">
        <i class="ri-information-line mt-1" style="color:#3b82f6;flex-shrink:0;"></i>
        <div><strong>Quantity Rules:</strong> Minimum order is <?php echo e($ballsMin); ?> balls. You can order up to <?php echo e($ballsMax); ?> balls per batch. Orders must be in multiples of 5.</div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=600&q=80" alt="BTS Mystery Balls" class="product-img h-100">
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['id' => 'discount-badge','label' => isset($ballPacks[0]['percent']) ? '-' . $ballPacks[0]['percent'] . '%' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'discount-badge','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(isset($ballPacks[0]['percent']) ? '-' . $ballPacks[0]['percent'] . '%' : null)]); ?>
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
                        <span class="position-absolute top-0 start-0 m-3 badge" style="background:<?php echo e($theme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;">ARMY Special</span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-center h-100">
                            <h3 class="fw-bold mb-3"><?php echo e($biasLabel ? $btsLabel.' Mystery Balls' : 'BTS Mystery Balls'); ?></h3>
                            <p class="text-muted mb-4">Surprise mystery balls packed with BTS-themed goodies<?php echo e($biasLabel ? ' for '.$biasLabel : ''); ?>! Same high-quality items as our scoops.</p>
                            <div class="mb-4">
                                <?php echo $__env->make('pages.partials.balls-pricing', ['packs' => $ballPacks, 'mode' => $ballsMode, 'accent' => $theme['accent']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                            <div class="bg-light rounded-3 p-4 text-center">
                                <div class="fw-medium text-muted small mb-3">Select Quantity (Multiples of 5)</div>
                                <div class="d-flex align-items-center justify-content-center gap-4">
                                    <button class="qty-btn" id="qty-minus" onclick="updateQtyBalls(-5)"><i class="ri-subtract-line"></i></button>
                                    <div>
                                        <div class="fw-bold" id="qty-display" style="font-size:2.5rem;line-height:1;"><?php echo e($ballsMin); ?></div>
                                        <div class="text-muted" style="font-size:.75rem;">Balls</div>
                                    </div>
                                    <button class="qty-btn" id="qty-plus" onclick="updateQtyBalls(5)"><i class="ri-add-line"></i></button>
                                </div>
                                <div class="text-muted mt-2" style="font-size:.8rem;">Range: <?php echo e($ballsMin); ?> - <?php echo e($ballsMax); ?> balls</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">💜 How BTS Mystery Balls Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">💜 Step 1: Your Bias Selection</div>
                    <p class="text-muted small mb-0">You selected <strong><?php echo e($biasLabel ?? 'BTS'); ?></strong><?php echo e($era ? ' · '.$era : ''); ?>. Each ball will be themed around your bias.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 2: Choose Your Pack Size</div>
                    <p class="text-muted small mb-0">Select how many Mystery Balls you'd like. You can choose any quantity from 5 to 50 balls in increments of 5.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 3: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose the number of packs you'd like to order. You can mix different Mystery Ball collections in the same order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 4: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Use the comment box before adding to cart to let us know:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred member items or era themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Any replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll do our best to personalize your mystery experience.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 5: We Curate Your ARMY Mystery</div>
                    <p class="text-muted small mb-1">Every Mystery Ball is individually packed with BTS-themed goodies and care. No two balls are the same!</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If your order contains too many identical items, we'll replace some with similar items to keep your mystery fun and varied.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 6: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your BTS Mystery Balls beautifully packed and ready to open. Enjoy discovering what's inside and share the excitement! 🎉 Borahae! 💜</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review['name']); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review['date']); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review['stars']?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-0"><?php echo e($review['text']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <?php if($biasLabel): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Bias</span>
                    <span class="fw-semibold" style="color:<?php echo e($theme['accent']); ?>;"><?php echo e($btsLabel); ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="summary-qty"><?php echo e($ballsMin); ?> Balls</span>
                </div>
                <?php if($ballsMode === \App\Models\Product::PRICING_AUTOMATIC && $ballPacks): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Price per 5</span>
                    <span class="fw-semibold balls-base-price"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $ballPacks[0]['price'] * 5 / $ballsMin,'original' => $ballPacks[0]['original'] !== null ? $ballPacks[0]['original'] * 5 / $ballsMin : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['price'] * 5 / $ballsMin),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['original'] !== null ? $ballPacks[0]['original'] * 5 / $ballsMin : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-4 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="summary-total" style="color:<?php echo e($theme['accent']); ?>;"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $ballPacks[0]['price'] ?? null,'original' => $ballPacks[0]['original'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['price'] ?? null),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ballPacks[0]['original'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></span>
                </div>
                <div class="mb-4">
                    <label class="fw-medium small mb-2 d-block">Special Requests (Optional)</label>
                    <textarea class="form-control" rows="4" id="bb-special-requests" placeholder="E.g., member preferences, era themes..." style="border-radius:.5rem;font-size:.875rem;resize:none;border:1px solid #e5e7eb;"></textarea>
                </div>
                <button onclick="addToCart('bts-balls')" id="balls-add-btn" <?php if(! $ballPacks || ! $ballPacks[0]['available']): echo 'disabled'; endif; ?> class="btn text-white w-100 py-2 fw-semibold" style="background:<?php echo e($theme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;">
                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart — <span id="btn-total">PKR <?php echo e(number_format($ballPacks[0]['price'] ?? 0)); ?></span>
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>



<?php elseif(in_array($productType, ['ceramic', 'gift-box'])): ?>

    <?php
        $isCeramic = $productType === 'ceramic';

        // Gift box catalogue — keyed by item ID from ?item= param
        $giftBoxCatalogue = [
            'love-yourself'   => ['name'=>'Love Yourself',    'desc'=>'BTS Love Yourself era themed mystery box',    'price'=>'PKR 6,900',  'image'=>'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80'],
            'map-of-the-soul' => ['name'=>'Map of the Soul',  'desc'=>'Curated items from the MOTS era',             'price'=>'PKR 6,200',  'image'=>'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=600&q=80'],
            'dynamite-retro'  => ['name'=>'Dynamite Retro',   'desc'=>'Retro vibes from the Dynamite era',           'price'=>'PKR 5,500',  'image'=>'https://images.unsplash.com/photo-1607344645866-009c320b63e0?w=600&q=80'],
            'butter-summer'   => ['name'=>'Butter Summer',    'desc'=>'Sweet summer Butter era collectibles',        'price'=>'PKR 6,000',  'image'=>'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=600&q=80'],
            'borachae-set'    => ['name'=>'Borachae Set',     'desc'=>'Purple-themed ARMY exclusive set',            'price'=>'PKR 9,500',  'image'=>'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80'],
            '10th-anniversary'=> ['name'=>'10th Anniversary', 'desc'=>'Celebrating 10 years of BTS',                'price'=>'PKR 13,500', 'image'=>'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=600&q=80'],
            'tata-space'      => ['name'=>"Tata's Space",     'desc'=>'Themed gift boxes for Tata lovers',          'price'=>'PKR 4,500',  'image'=>'https://images.unsplash.com/photo-1607344645866-009c320b63e0?w=600&q=80'],
            'cooky-box'       => ['name'=>'Cooky Box',        'desc'=>'Adorable Cooky BT21 mystery box',            'price'=>'PKR 4,200',  'image'=>'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=600&q=80'],
        ];

        $itemId     = request('item');
        $giftBoxItem = !$isCeramic && $itemId && isset($giftBoxCatalogue[$itemId])
                        ? $giftBoxCatalogue[$itemId]
                        : null;

        $pageTitle  = $isCeramic ? 'Ceramic Collection' : ($giftBoxItem ? $giftBoxItem['name'] : "Tata's Space");
        $pageDesc   = $isCeramic
            ? 'Handpicked, aesthetic ceramic pieces to brighten up your home and daily routine.'
            : ($giftBoxItem ? $giftBoxItem['desc'] : 'Themed gift boxes for Tata lovers — beautifully curated BTS-inspired goodies.');
        $productName  = $isCeramic ? 'Cute Ceramic Mug' : ($giftBoxItem ? $giftBoxItem['name'] : "Tata's Space Gift Box");
        $productDesc  = $isCeramic
            ? 'Adorable ceramic mug with cute designs. Perfect for your morning coffee!'
            : ($giftBoxItem ? $giftBoxItem['desc'] : 'A beautifully themed gift box packed with Tata-inspired goodies and BTS surprises.');
        $productPrice = $isCeramic ? 'PKR 1,500' : ($giftBoxItem ? $giftBoxItem['price'] : 'PKR 4,500');
        $productImg   = $isCeramic
            ? 'https://images.unsplash.com/photo-1515401857507-c897a8f64e42?w=600&q=80'
            : ($giftBoxItem ? $giftBoxItem['image'] : 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80');
    ?>
    <?php $__env->startSection('title', $pageTitle); ?>

    <div class="text-center mb-5">
        <?php if(!$isCeramic): ?>
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($theme['accent']); ?>,<?php echo e($theme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <?php echo e($pageTitle); ?>

        </h1>
        <?php else: ?>
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,5vw,3.5rem);color:#5c3d1e;">
            <?php echo e($pageTitle); ?>

        </h1>
        <?php endif; ?>
        <p class="text-muted mx-auto" style="max-width:36rem;font-size:1.05rem;"><?php echo e($pageDesc); ?></p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="<?php echo e($productImg); ?>" alt="<?php echo e($productName); ?>" class="product-img h-100">
                        <button class="position-absolute top-0 end-0 m-3 btn btn-light btn-sm rounded-circle p-1" style="width:2.2rem;height:2.2rem;" title="Add to Wishlist">
                            <i class="ri-heart-line" style="color:#9ca3af;"></i>
                        </button>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-start h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="fw-bold mb-0"><?php echo e($productName); ?></h3>
                                <span class="fw-bold" style="color:<?php echo e($isCeramic ? '#5c3d1e' : $theme['accent']); ?>;font-size:1.1rem;"><?php echo e($productPrice); ?></span>
                            </div>
                            <p class="text-muted small mb-3"><?php echo e($productDesc); ?></p>

                            <div class="row g-2 mb-3">
                                <?php if($isCeramic): ?>
                                <div class="col-6">
                                    <div class="spec-box">
                                        <div class="spec-label">Material</div>
                                        <div class="spec-value">High-grade Ceramic</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="spec-box">
                                        <div class="spec-label">Capacity</div>
                                        <div class="spec-value">350ml – 400ml</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex align-items-center gap-3 mt-auto">
                                <button class="qty-btn" id="ceramic-minus" onclick="updateQtyCeramic(-1)" disabled><i class="ri-subtract-line"></i></button>
                                <span class="fw-bold fs-5" id="ceramic-qty-display">1</span>
                                <button class="qty-btn" id="ceramic-plus" onclick="updateQtyCeramic(1)"><i class="ri-add-line"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review['name']); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review['date']); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review['stars']?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <p class="text-muted small mb-0"><?php echo e($review['text']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Item</span>
                    <span class="fw-semibold" style="font-size:.85rem;"><?php echo e($productName); ?></span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="ceramic-summary-qty">1</span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Price per item</span>
                    <span class="fw-semibold"><?php echo e($productPrice); ?></span>
                </div>
                <div class="mb-2 d-flex justify-content-between fw-bold fs-5">
                    <span>Total</span>
                    <span id="ceramic-summary-total" style="color:<?php echo e($theme['accent']); ?>;"><?php echo e($productPrice); ?></span>
                </div>
                <div class="mb-4"></div>
                <button onclick="addToCart('<?php echo e($productType); ?>')" class="btn text-white w-100 py-2 fw-semibold" style="background:<?php echo e($theme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;color:#fff!important;">
                    <i class="ri-shopping-cart-line me-2"></i>Add to Cart
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>




<?php elseif($productType === 'product-item'): ?>
<?php $__env->startSection('title', $product->name); ?>

<?php
    /** @var \App\Models\Product $product */
    $categoryName   = $product->category?->name ?? '';
    $subCategoryName = $product->subCategory?->name ?? '';

    // Resolve theme based on category
    $resolvedThemeKey = match(true) {
        str_contains($categoryName, 'Ceramic')        => 'ceramic',
        str_contains($categoryName, 'BTS Gift Boxes') ||
        str_contains($categoryName, 'BTS Merch')       => 'bts-scoop',
        str_contains($categoryName, 'Gift Boxes') ||
        str_contains($categoryName, 'Cartoon')         => 'gift-box',
        str_contains($categoryName, 'Mystery Scoops')  => 'mystery-scoops',
        str_contains($categoryName, 'Mystery Balls')   => 'mystery-balls',
        default => 'gift-box',
    };
    $piTheme = $themes[$resolvedThemeKey] ?? $themes['gift-box'];

    $isCeramicItem  = str_contains($categoryName, 'Ceramic');
    // Mystery Balls products (automatic or custom pricing) sell one "Pack of N" variant per quantity
    $isBalls        = $product->usesPackPricing();

    $productImg     = $product->images->first()?->image_url
                        ?? 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80';

    $variants       = $product->variants->sortBy('price')->values();
    $hasMultiVariants = $variants->count() > 1;

    // Balls: the stepper walks the pack options and each pack is a single
    // cart line priced as a whole, so the starting selection is the smallest pack.
    $ballPacks = $isBalls ? $product->packOptions() : [];
    $ballsMode = $product->pricing_mode;
    $ballsMin  = $ballPacks[0]['qty'] ?? 5;
    $ballsMax  = $ballPacks ? $ballPacks[count($ballPacks) - 1]['qty'] : 50;

    $firstVariant   = $isBalls ? $variants->firstWhere('id', $ballPacks[0]['id'] ?? null) : $variants->first();
    $minPrice       = $firstVariant?->effectivePrice() ?? 0;
    $minOriginal    = $firstVariant?->hasDiscount() ? (float) $firstVariant->price : null;
    $isPreorderItem = $firstVariant?->status === \App\Models\ProductVariant::STATUS_PRE_ORDER;
    $piInitialUnitPrice = $minPrice;
    $piVariants = $variants->mapWithKeys(fn ($v) => [$v->id => [
        'name'     => $v->variant_name,
        'price'    => $v->effectivePrice(),
        'original' => $v->hasDiscount() ? (float) $v->price : null,
        'percent'  => $v->discountPercent(),
        'preorder' => $v->isPreOrder(),
    ]])->all();

    // Breadcrumb label
    $breadcrumbLabel = $subCategoryName ?: $categoryName;
?>

    
    <div class="text-center mb-5">
        <?php if($subCategoryName): ?>
            <div class="bts-info-badge" style="background:color-mix(in srgb, <?php echo e($piTheme['accent']); ?> 12%, white);border-color:color-mix(in srgb, <?php echo e($piTheme['accent']); ?> 40%, white);color:<?php echo e($piTheme['accent']); ?>;">
                <i class="ri-price-tag-3-line"></i> <?php echo e($breadcrumbLabel); ?>

            </div>
        <?php endif; ?>
        <h1 class="fw-bold mb-3" data-pi-name
            style="font-size:clamp(2rem,5vw,3.5rem);background:linear-gradient(to right,<?php echo e($piTheme['accent']); ?>,<?php echo e($piTheme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            <?php echo e($product->name); ?>

        </h1>
        <p class="text-muted mx-auto" style="max-width:40rem;font-size:1.05rem;" data-pi-description>
            <?php echo e($product->description); ?>

        </p>
    </div>

    <?php if($isBalls): ?>
    <div class="info-alert mb-4 d-flex gap-2 align-items-start">
        <i class="ri-information-line mt-1" style="color:#3b82f6;flex-shrink:0;"></i>
        <div><strong>Quantity Rules:</strong> Minimum order is <?php echo e($ballsMin); ?> balls. Orders must be in multiples of 5 (e.g., 5, 10, 15…). Maximum <?php echo e($ballsMax); ?> per batch.</div>
    </div>
    <?php endif; ?>

    <div class="info-preorder mb-4 gap-2 align-items-start <?php echo e($isPreorderItem ? 'd-flex' : 'd-none'); ?>" id="pi-preorder-banner">
        <i class="ri-alarm-warning-line mt-1" style="color:#d97706;flex-shrink:0;"></i>
        <div>
            <strong style="color:#92400e;">Pre-order Information</strong><br>
            <span class="text-muted small">A </span>
            <strong style="color:#d97706;">40% advance payment of the total</strong>
            <span class="text-muted small"> is required to confirm your pre-order. There is no cash on delivery — the remaining balance is charged once the item is restocked. Estimated restock: 2–3 weeks.</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            
            <div class="product-card card mb-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <img src="<?php echo e($productImg); ?>" alt="<?php echo e($product->name); ?>" class="product-img h-100">
                        <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['id' => 'discount-badge','label' => $firstVariant?->discountPercent() ? '-' . $firstVariant->discountPercent() . '%' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'discount-badge','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($firstVariant?->discountPercent() ? '-' . $firstVariant->discountPercent() . '%' : null)]); ?>
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
                        <?php if($subCategoryName): ?>
                            <span class="position-absolute top-0 start-0 m-3 badge"
                                  style="background:<?php echo e($piTheme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;">
                                <?php echo e($subCategoryName); ?>

                            </span>
                        <?php endif; ?>
                        <span class="position-absolute top-0 end-0 m-3 badge" id="pi-preorder-badge"
                              style="background:<?php echo e($piTheme['badgeBg']); ?>;font-size:.75rem;padding:.4rem .75rem;border-radius:2rem;<?php echo e($isPreorderItem ? '' : 'display:none;'); ?>">
                            Pre-order
                        </span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 d-flex flex-column justify-content-start h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="fw-bold mb-0" data-pi-name><?php echo e($product->name); ?></h3>
                                <span class="fw-bold" style="color:<?php echo e($piTheme['accent']); ?>;font-size:1.1rem;" data-pi-headline>
                                    <?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $minPrice,'original' => $minOriginal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minPrice),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minOriginal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-3" data-pi-description><?php echo e($product->description); ?></p>

                            <?php if($isCeramicItem && $firstVariant): ?>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="spec-box">
                                        <div class="spec-label">Material</div>
                                        <div class="spec-value">High-grade Ceramic</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="spec-box">
                                        <div class="spec-label">Size</div>
                                        <div class="spec-value"><?php echo e($firstVariant->variant_name); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            
                            <?php if($isBalls): ?>
                            <div class="mb-3">
                                <?php echo $__env->make('pages.partials.balls-pricing', ['packs' => $ballPacks, 'mode' => $ballsMode, 'accent' => $piTheme['accent']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                            <?php elseif($hasMultiVariants): ?>
                            <div class="mb-3">
                                <div class="fw-medium small mb-2">Select Variant</div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button class="size-btn <?php echo e($i === 0 ? 'active' : ''); ?>" data-variant-id="<?php echo e($variant->id); ?>"
                                            onclick="piSelectVariant(this, <?php echo e($variant->id); ?>)">
                                        <div class="size-name"><?php echo e($variant->variant_name); ?></div>
                                        <div class="size-price"><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $variant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?></div>
                                    </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <?php else: ?>
                            
                            <div class="mb-3">
                                <span class="fw-bold" id="pi-display-price"
                                      style="font-size:1.5rem;color:<?php echo e($piTheme['accent']); ?>;">
                                    <?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['amount' => $minPrice,'original' => $minOriginal]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minPrice),'original' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minOriginal)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
                                </span>
                                <?php if($firstVariant): ?>
                                    <span class="text-muted small ms-1" id="pi-display-variant"><?php echo e($firstVariant->variant_name); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            
                            <div class="mb-3 mt-auto">
                                <?php if($isBalls): ?>
                                    <div class="fw-medium small mb-2">Quantity <span class="text-muted">(Multiples of 5, max <?php echo e($ballsMax); ?>)</span></div>
                                    <div class="d-flex align-items-center gap-3">
                                        <button class="qty-btn" id="pi-qty-minus" onclick="updateQtyBalls(-5)" disabled><i class="ri-subtract-line"></i></button>
                                        <span class="fw-bold fs-5" id="pi-qty-display"><?php echo e($ballsMin); ?></span>
                                        <button class="qty-btn" id="pi-qty-plus"  onclick="updateQtyBalls(5)" <?php if(count($ballPacks) <= 1): echo 'disabled'; endif; ?>><i class="ri-add-line"></i></button>
                                    </div>
                                <?php else: ?>
                                    <div class="fw-medium small mb-2">Quantity <span class="text-muted">(Min: 1, Max: <?php echo e($firstVariant?->max_qty ?? 10); ?>)</span></div>
                                    <div class="d-flex align-items-center gap-3">
                                        <button class="qty-btn" id="pi-qty-minus" onclick="piUpdateQty(-1)" disabled><i class="ri-subtract-line"></i></button>
                                        <span class="fw-bold fs-5" id="pi-qty-display">1</span>
                                        <button class="qty-btn" id="pi-qty-plus"  onclick="piUpdateQty(1)"><i class="ri-add-line"></i></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if($resolvedThemeKey === 'mystery-scoops'): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">🎁 How Mystery Scoops Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Scoop Size</div>
                    <p class="text-muted small mb-1">Select your Mystery Scoop size:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Classic Scoop</li><li>Standard Scoop</li><li>Premium Scoop</li></ul>
                    <p class="text-muted small mb-0">Each size comes with a different value and surprise level.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose how many scoops you want (minimum 1, maximum 20 per size). You can also mix different sizes with different quantities in one order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Before adding to cart, use the comment box to tell us:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll try our best to personalize your mystery.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate the Surprise</div>
                    <p class="text-muted small mb-1">Our team handpicks each scoop with care. No two Mystery Scoops are the same — that's the magic!</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If more than 4 identical items are selected, they'll be upgraded automatically to keep your mystery exciting.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your Mystery Scoops beautifully packed and ready to surprise you. Open, explore, and enjoy the thrill of the unknown 🎉</p>
                </div>
            </div>
            <?php elseif($resolvedThemeKey === 'mystery-balls'): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">🎁 How Mystery Balls Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Pack Size</div>
                    <p class="text-muted small mb-0">Select how many Mystery Balls you'd like. You can choose any quantity from 5 to 50 balls in increments of 5.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose the number of packs you'd like to order. You can mix different Mystery Ball collections in the same order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Use the comment box before adding to cart to let us know:</p>
                    <ul class="text-muted small mb-1 ps-3">
                        <li>Preferred themes or characters</li>
                        <li>Items you'd love to receive</li>
                        <li>Items you'd like to avoid</li>
                        <li>Any replacement suggestions</li>
                    </ul>
                    <p class="text-muted small mb-0">We'll do our best to personalize your mystery experience.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate Your Mystery</div>
                    <p class="text-muted small mb-1">Every Mystery Ball is packed with care to keep every order exciting and unique.</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If your order contains too many identical items, we'll replace some with similar items to keep your mystery fun and varied.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Enjoy the Surprise</div>
                    <p class="text-muted small mb-0">Your Mystery Balls arrive beautifully packed and ready to open. Enjoy discovering what's inside and share the excitement! 🎉</p>
                </div>
            </div>
            <?php elseif(str_contains($categoryName, 'BTS Merch') && $isBalls): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">💜 How BTS Mystery Balls Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Pack Size</div>
                    <p class="text-muted small mb-0">Select how many Mystery Balls you'd like. You can choose any quantity from 5 to 50 balls in increments of 5.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose the number of packs you'd like to order. You can mix different Mystery Ball collections in the same order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Use the comment box before adding to cart to let us know:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred member items or era themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Any replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll do our best to personalize your mystery experience.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate Your ARMY Mystery</div>
                    <p class="text-muted small mb-1">Every Mystery Ball is individually packed with BTS-themed goodies and care. No two balls are the same!</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If your order contains too many identical items, we'll replace some with similar items to keep your mystery fun and varied.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your BTS Mystery Balls beautifully packed and ready to open. Enjoy discovering what's inside and share the excitement! 🎉 Borahae! 💜</p>
                </div>
            </div>
            <?php elseif(str_contains($categoryName, 'BTS Merch')): ?>
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold mb-4">💜 How BTS Mystery Scoops Work</h5>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🛒 Step 1: Choose Your Scoop Size</div>
                    <p class="text-muted small mb-1">Select your Mystery Scoop size:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Classic Scoop</li><li>Standard Scoop</li><li>Premium Scoop</li></ul>
                    <p class="text-muted small mb-0">Each size comes with a different value and surprise level.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🔢 Step 2: Select Quantity</div>
                    <p class="text-muted small mb-0">Choose how many scoops you want (minimum 1, maximum 20 per size). You can also mix different sizes with different quantities in one order.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">✍️ Step 3: Add Special Requests (Optional)</div>
                    <p class="text-muted small mb-1">Before adding to cart, use the comment box to tell us:</p>
                    <ul class="text-muted small mb-1 ps-3"><li>Preferred member items or era themes</li><li>Items you'd love to receive</li><li>Items you'd like to avoid</li><li>Replacement suggestions</li></ul>
                    <p class="text-muted small mb-0">We'll try our best to personalize your mystery.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">🎲 Step 4: We Curate the ARMY Surprise</div>
                    <p class="text-muted small mb-1">Our team handpicks each scoop with BTS-themed care. Borahae! 💜</p>
                    <p class="text-muted small mb-0">👉 <strong>Note:</strong> If more than 4 identical items are selected, they'll be upgraded automatically to keep your mystery exciting.</p>
                </div>
                <div class="how-step">
                    <div class="fw-semibold mb-1">📦 Step 5: Unbox the Fun</div>
                    <p class="text-muted small mb-0">Receive your BTS Mystery Scoop beautifully packed and ready to surprise you. Open, explore, and enjoy the thrill of the unknown 🎉</p>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
                        onclick="toggleReviews(this)" aria-expanded="true">
                    <h5 class="fw-bold mb-0">Reviews</h5>
                    <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
                </button>
                <div id="reviews-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="fw-bold fs-5"><?php echo e(number_format($avgRating, 1)); ?></span>
                        <div>
                            <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=round($avgRating)?'fill':'line'); ?> star"></i><?php endfor; ?>
                        </div>
                        <span class="text-muted small">(<?php echo e($reviewCount); ?> review<?php echo e($reviewCount === 1 ? '' : 's'); ?>)</span>
                    </div>
                    <?php $__empty_1 = true; $__currentLoopData = $productReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small"><?php echo e($review->user->name ?? 'Anonymous'); ?></span>
                                <span class="text-muted" style="font-size:.75rem;"><?php echo e($review->created_at->format('d M Y')); ?></span>
                            </div>
                            <div class="mb-2">
                                <?php for($s=1;$s<=5;$s++): ?><i class="ri-star-<?php echo e($s<=$review->rating?'fill':'line'); ?> star"></i><?php endfor; ?>
                            </div>
                            <?php if($review->comments): ?>
                            <p class="text-muted small mb-2"><?php echo e($review->comments); ?></p>
                            <?php endif; ?>
                            <?php if($review->images->isNotEmpty()): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $__currentLoopData = $review->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reviewImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <img src="<?php echo e(asset('images/reviews/' . $reviewImage->image_url)); ?>"
                                     alt="Review image" class="review-img">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted small mb-0">No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="summary-card card p-4">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                <?php if($subCategoryName): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Collection</span>
                    <span class="fw-semibold" style="font-size:.85rem;"><?php echo e($subCategoryName); ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Product</span>
                    <span class="fw-semibold" style="font-size:.82rem;" data-pi-name><?php echo e($product->name); ?></span>
                </div>
                <?php if($hasMultiVariants): ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted"><?php echo e($isBalls ? 'Pack' : 'Variant'); ?></span>
                    <span class="fw-semibold" id="pi-summary-variant"><?php echo e($firstVariant?->variant_name); ?></span>
                </div>
                <?php endif; ?>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted">Quantity</span>
                    <span class="fw-semibold" id="pi-summary-qty"><?php echo e($isBalls ? $ballsMin . ' Balls' : '1'); ?></span>
                </div>
                <div class="mb-3 pb-3 border-bottom d-flex justify-content-between">
                    <span class="text-muted"><?php echo e(! $isBalls ? 'Unit Price' : ($ballsMode === \App\Models\Product::PRICING_AUTOMATIC ? 'Price per 5' : 'Pack Price')); ?></span>
                    <span class="fw-semibold" id="pi-summary-unit">PKR <?php echo e(number_format($piInitialUnitPrice)); ?></span>
                </div>
                <div class="mb-2 d-flex justify-content-between fw-bold fs-5">
                    <span id="pi-summary-total-label"><?php echo e($isPreorderItem ? 'Advance (40%)' : 'Total'); ?></span>
                    <span id="pi-summary-total" style="color:<?php echo e($piTheme['accent']); ?>;">
                        PKR <?php echo e($isPreorderItem ? number_format(round($minPrice * 0.4)) : number_format($minPrice)); ?>

                    </span>
                </div>
                <div id="pi-remaining-note-box" class="mb-4 text-muted small" style="background:#fffbeb;border-radius:.4rem;padding:.5rem .75rem;border:1px solid #fcd34d;margin-top:.5rem;<?php echo e($isPreorderItem ? '' : 'display:none;'); ?>">
                    <i class="ri-information-line me-1" style="color:#d97706;"></i>
                    40% advance required to confirm pre-order. Remaining balance is charged once restocked — no cash on delivery.
                </div>
                <div id="pi-spacer" class="mb-4" style="<?php echo e($isPreorderItem ? 'display:none;' : ''); ?>"></div>
                <div class="mb-4">
                    <label class="fw-medium small mb-2 d-block">Special Requests (Optional)</label>
                    <textarea class="form-control" rows="3" id="pi-special-requests"
                              placeholder="E.g., preferred themes, items to avoid..."
                              style="border-radius:.5rem;font-size:.875rem;resize:none;border:1px solid #e5e7eb;"></textarea>
                </div>
                <button onclick="piAddToCart()" id="pi-add-btn" <?php if($isBalls && ! ($ballPacks[0]['available'] ?? false)): echo 'disabled'; endif; ?>
                        class="btn text-white w-100 py-2 fw-semibold"
                        style="background:<?php echo e($piTheme['btnGrad']); ?>;border-radius:.5rem;font-size:.9rem;">
                    <i class="ri-shopping-cart-line me-2"></i>
                    <span id="pi-btn-label"><?php echo e($isPreorderItem ? 'Pre-order Now' : 'Add to Cart'); ?></span> —
                    <span id="pi-btn-total">PKR <?php echo e($isPreorderItem ? number_format(round($minPrice * 0.4)) : number_format($minPrice)); ?></span>
                </button>
                <p class="text-center text-muted mt-3" style="font-size:.75rem;">Secure checkout powered by Ravellen</p>
            </div>
        </div>
    </div>

    <?php if($relatedProducts->isNotEmpty()): ?>
    
    <div class="mt-5">
        <h5 class="fw-bold mb-4">Related Products</h5>
        <div class="row g-3">
            <?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $relatedImg   = $related->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80';
                    $relatedCheapest = $related->cheapestVariant();
                ?>
                <div class="col-6 col-md-4 col-lg-3" data-live-product="<?php echo e($related->id); ?>">
                    <div class="related-prod-card">
                        <div class="related-prod-img-wrap">
                            <img src="<?php echo e($relatedImg); ?>" alt="<?php echo e($related->name); ?>" data-live-image>
                            <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['label' => $related->discountLabel(),'live' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($related->discountLabel()),'live' => true]); ?>
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
                        </div>
                        <div class="p-3">
                            <h6 class="related-prod-name" data-live-name><?php echo e($related->name); ?></h6>
                            <p class="related-prod-price" data-live-price data-live-prefix="From " style="background:linear-gradient(to right,<?php echo e($piTheme['accent']); ?>,<?php echo e($piTheme['accent2']); ?>);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                                <?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $relatedCheapest,'prefix' => 'From ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relatedCheapest),'prefix' => 'From ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
                            </p>
                            <a href="<?php echo e(route('product-item', $related->slug)); ?>" class="related-explore-btn" style="background:<?php echo e($piTheme['btnGrad']); ?>;">
                                View Product <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>


</div>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
<script>
// Blurred backdrop behind the fitted product photo (see .product-img-frame)
document.querySelectorAll('.product-img').forEach(img => {
    img.parentElement.classList.add('product-img-frame');
    img.parentElement.style.setProperty('--product-img', `url("${img.getAttribute('src')}")`);
});

// ── Shared helpers ─────────────────────────────────────────────────────────
const CART_KEY   = 'rv_cart';
const IS_LOGGED_IN = <?php echo e(auth()->check() ? 'true' : 'false'); ?>;
const CSRF_TOKEN   = '<?php echo e(csrf_token()); ?>';
const CART_STORE_URL   = '<?php echo e(route('cart.store')); ?>';
const CART_UPDATE_URL  = '/cart/items/';   // append {id}
const CART_DESTROY_URL = '/cart/items/';   // append {id}

function fmtPkr(n) { return 'PKR ' + Math.round(n).toLocaleString(); }
/* Same markup as the components/price.blade.php component: price, plus the crossed-out regular price while on sale. */
function priceHtml(amount, original) {
    return fmtPkr(amount) + (original && original > amount
        ? ' <s class="price-was" style="color:#9ca3af;-webkit-text-fill-color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">' + fmtPkr(original) + '</s>'
        : '');
}
/* "-15%" badge on the product image for the selected variant / pack; hidden when it isn't on sale. */
function setDiscountBadge(percent) {
    const badge = document.getElementById('discount-badge');
    if (!badge) return;
    badge.textContent   = percent ? '-' + percent + '%' : '';
    badge.style.display = percent ? '' : 'none';
}

/**
 * Persist item to DB (auth users) then write to localStorage.
 * For auth users: the API response gives back the canonical 'db-{id}'
 * which we store — so syncFromDb() on the cart page won't create a duplicate.
 * For guests: write the localItem directly (no DB call).
 */
async function persistToCart(payload, localItem) {
    if (IS_LOGGED_IN) {
        try {
            const res  = await fetch(CART_STORE_URL, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (res.ok) {
                const data = await res.json();
                // Use the server-assigned 'db-{id}' as canonical id
                const canonical = data.cartItem || {};
                const cart = JSON.parse(localStorage.getItem(CART_KEY) || '[]');

                // Remove any stale local copy of the same product/variant before writing
                const filtered = cart.filter(i => {
                    // keep non-pi items, and pi items for a different product
                    if (!i.id.startsWith('pi-')) return true;
                    return i.id !== localItem.id;
                });

                // Merge with any existing db-{id} entry (qty increment from server)
                const dbId    = canonical.id; // 'db-123'
                const already = filtered.find(i => i.id === dbId);
                if (already) {
                    already.qty     = canonical.qty;
                    already.comment = canonical.comment ?? already.comment;
                    // the server's price wins (a sale may have started or ended)
                    already.unitPrice     = canonical.unitPrice ?? already.unitPrice;
                    already.originalPrice = canonical.originalPrice ?? null;
                } else {
                    filtered.push({
                        id:        canonical.id        ?? localItem.id,
                        name:      canonical.name      ?? localItem.name,
                        subtitle:  canonical.subtitle  ?? localItem.subtitle,
                        image:     canonical.image     ?? localItem.image,
                        unitPrice: canonical.unitPrice ?? localItem.unitPrice,
                        originalPrice: canonical.unitPrice !== undefined ? (canonical.originalPrice ?? null) : localItem.originalPrice,
                        qty:       canonical.qty       ?? localItem.qty,
                        preorder:  canonical.preorder  ?? localItem.preorder,
                        comment:   canonical.comment    ?? localItem.comment,
                    });
                }

                localStorage.setItem(CART_KEY, JSON.stringify(filtered));
                if (window.updateCartBadge) updateCartBadge();
                return;
            } else {
                const err = await res.json().catch(() => ({}));
                console.error('Cart API error:', err);
            }
        } catch (e) {
            console.error('Cart persist failed:', e);
        }
    }

    // Guest fallback — or if the API call failed: write localItem to localStorage
    const cart     = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
    const existing = cart.find(i => i.id === localItem.id && i.subtitle === localItem.subtitle);
    if (existing) {
        existing.qty += localItem.qty;
    } else {
        cart.push(localItem);
    }
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    if (window.updateCartBadge) updateCartBadge();
}

const PRODUCT_META = {
    'mystery-balls':  { name: 'Mystery Balls',        image: 'https://images.unsplash.com/photo-1615485925694-a031e97d8964?w=200&q=80', preorder: false },
    'mystery-scoops': { name: 'Mystery Scoop',         image: 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=200&q=80', preorder: false },
    'bts-scoop':      { name: 'BTS Mystery Scoop',     image: 'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=200&q=80', preorder: false },
    'bts-balls':      { name: 'BTS Mystery Balls',     image: 'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=200&q=80', preorder: false },
    'ceramic':        { name: 'Cute Ceramic Mug',      image: 'https://images.unsplash.com/photo-1515401857507-c897a8f64e42?w=200&q=80', preorder: false },
    'gift-box':       { name: '<?php echo e(addslashes($productName ?? "Tata's Space Gift Box")); ?>', image: '<?php echo e($productImg ?? 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80'); ?>', preorder: false },
};

const GIFT_BOX_ITEM_ID = '<?php echo e($itemId ?? 'gift-box'); ?>';

// DB product injected from PHP for the generic mystery types (no product_id = localStorage only)
const DB_PRODUCT_MAP = <?php echo json_encode($dbProductMap ?? ['product_id' => null], 15, 512) ?>;

// Maps each generic product type to its "Special Requests" textarea id
const SPECIAL_REQUESTS_FIELD = {
    'mystery-balls':  'mb-special-requests',
    'mystery-scoops': 'ms-special-requests',
    'bts-scoop':      'bs-special-requests',
    'bts-balls':      'bb-special-requests',
};

function addToCart(type) {
    const meta = PRODUCT_META[type] || {};
    let unitPrice, qty, subtitle, variantId = null, originalPrice = null;

    if (type === 'mystery-balls' || type === 'bts-balls') {
        // The selected pack (e.g. "Pack of 10") is one cart line priced as a whole
        const pack = currentBallsPack();
        if (!pack || !pack.available) return;
        variantId     = pack.id;
        unitPrice     = pack.price;
        originalPrice = pack.original;
        qty           = 1;
        subtitle      = 'Pack of ' + pack.qty;

    } else if (type === 'mystery-scoops' || type === 'bts-scoop') {
        const scoop = SCOOP_OPTIONS[scoopIdx];
        if (!scoop) return;
        variantId     = scoop.id;
        unitPrice     = scoop.price;
        originalPrice = scoop.original;
        qty           = scoopQty;
        subtitle      = 'Size: ' + scoop.name;

    } else if (type === 'ceramic' || type === 'gift-box') {
        unitPrice = parseInt('<?php echo e(preg_replace('/[^0-9]/', '', $productPrice ?? 'PKR 1,500')); ?>') || 1500;
        qty       = ceramicQty;
        subtitle  = null;

    } else {
        unitPrice = 0; qty = 1; subtitle = null;
    }

    const fieldId = SPECIAL_REQUESTS_FIELD[type];
    const comment = fieldId ? (document.getElementById(fieldId)?.value?.trim() || null) : null;

    const cartId   = (type === 'gift-box') ? GIFT_BOX_ITEM_ID : type;
    const localItem = {
        id:        cartId,
        name:      meta.name || type,
        subtitle:  subtitle,
        image:     meta.image || '',
        unitPrice: unitPrice,
        originalPrice: originalPrice,
        qty:       qty,
        preorder:  meta.preorder || false,
        comment:   comment,
    };

    // Build DB payload when we have a product_id
    const productId = DB_PRODUCT_MAP.product_id ?? null;
    if (productId) {
        const dbPayload = {
            product_id:         productId,
            product_variant_id: variantId,
            qty:                qty,
            comment:            comment,
        };
        persistToCart(dbPayload, localItem);
    } else {
        // No DB product available (e.g. ceramic/gift-box sub-products) — localStorage only
        const cart     = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
        const existing = cart.find(i => i.id === cartId && i.subtitle === subtitle);
        if (existing) {
            existing.qty += qty;
        } else {
            cart.push(localItem);
        }
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        if (window.updateCartBadge) updateCartBadge();
    }
}

// ── Mystery Balls (generic + product-item pages) ───────────────────────────
// Quantity options are the product's "Pack of N" variants (Product::packOptions()),
// each already priced by the product's automatic or custom pricing with any
// sale applied. The stepper walks through them in order.
const BALL_PACKS = <?php echo json_encode($ballPacks ?? [], 15, 512) ?>;
let ballsIdx = 0;

function currentBallsPack() { return BALL_PACKS[ballsIdx] || null; }

// The ± buttons pass ±5; each press moves to the next/previous quantity on offer.
function updateQtyBalls(change) {
    selectBallsPack(ballsIdx + Math.sign(change));
}

function selectBallsPack(idx) {
    ballsIdx = Math.min(BALL_PACKS.length - 1, Math.max(0, idx));
    const pack = currentBallsPack();
    if (!pack) return;

    // Custom-pricing price list (pages.partials.balls-pricing)
    document.querySelectorAll('.balls-pack-btn').forEach(b => b.classList.toggle('active', Number(b.dataset.idx) === ballsIdx));
    document.querySelectorAll('.balls-price-qty').forEach(el => el.textContent = pack.qty);
    document.querySelectorAll('.balls-price').forEach(el => el.innerHTML = priceHtml(pack.price, pack.original));
    setDiscountBadge(pack.percent);

    // Generic mystery-balls / bts-balls page
    const qtyEl    = document.getElementById('qty-display');
    const sumQty   = document.getElementById('summary-qty');
    const sumTotal = document.getElementById('summary-total');
    const btnTotal = document.getElementById('btn-total');
    const addBtn   = document.getElementById('balls-add-btn');
    const minus    = document.getElementById('qty-minus');
    const plus     = document.getElementById('qty-plus');
    if (qtyEl)    qtyEl.textContent = pack.qty;
    if (sumQty)   sumQty.textContent = pack.qty + ' Balls';
    if (sumTotal) sumTotal.innerHTML = priceHtml(pack.price, pack.original);
    if (btnTotal) btnTotal.textContent = fmtPkr(pack.price);
    if (addBtn)   addBtn.disabled = !pack.available;
    if (minus)    minus.disabled = ballsIdx <= 0;
    if (plus)     plus.disabled  = ballsIdx >= BALL_PACKS.length - 1;

    // Product-item page
    if (typeof piApplyBallsPack === 'function') piApplyBallsPack(pack);
}

// ── Mystery Scoops / BTS Scoop ─────────────────────────────────────────────
// Sizes are the product's variants, cheapest first, with any sale applied.
const SCOOP_OPTIONS = <?php echo json_encode($scoopOptions ?? [], 15, 512) ?>;
let scoopQty = 1;
let scoopIdx = 0;

function selectSize(btn, idx) {
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    scoopIdx = idx;
    const scoop = SCOOP_OPTIONS[idx];
    const dp = document.getElementById('display-price');
    const dl = document.getElementById('display-size-label');
    if (dp) dp.innerHTML = priceHtml(scoop.price, scoop.original);
    if (dl) dl.textContent = 'per ' + scoop.name.toLowerCase();
    setDiscountBadge(scoop.percent);
    updateScoopSummary();
}

function updateQtyScoop(change) {
    scoopQty = Math.min(20, Math.max(1, scoopQty + change));
    const minus = document.getElementById('scoop-minus');
    const plus  = document.getElementById('scoop-plus');
    const disp  = document.getElementById('scoop-qty-display');
    if (minus) minus.disabled = scoopQty <= 1;
    if (plus)  plus.disabled  = scoopQty >= 20;
    if (disp)  disp.textContent = scoopQty;
    updateScoopSummary();
}

function updateScoopSummary() {
    const scoop = SCOOP_OPTIONS[scoopIdx];
    if (!scoop) return;
    const ss = document.getElementById('summary-size');
    const sq = document.getElementById('summary-qty');
    const su = document.getElementById('summary-unit-price');
    const st = document.getElementById('summary-total');
    const bt = document.getElementById('btn-total');
    if (ss) ss.textContent = scoop.name;
    if (sq) sq.textContent = scoopQty;
    if (su) su.innerHTML = priceHtml(scoop.price, scoop.original);
    if (st) st.innerHTML = priceHtml(scoop.price * scoopQty, scoop.original ? scoop.original * scoopQty : null);
    if (bt) bt.textContent = fmtPkr(scoop.price * scoopQty);
}

// ── Ceramic / Gift Box ─────────────────────────────────────────────────────
let ceramicQty = 1;
// Use PHP-resolved price (works for both ceramic PKR 1,500 and any gift box price)
const CERAMIC_UNIT_PRICE = parseInt('<?php echo e(preg_replace('/[^0-9]/', '', $productPrice ?? 'PKR 1,500')); ?>') || 1500;

function updateQtyCeramic(change) {
    ceramicQty = Math.min(10, Math.max(1, ceramicQty + change));
    const total = ceramicQty * CERAMIC_UNIT_PRICE;
    const minus = document.getElementById('ceramic-minus');
    const plus  = document.getElementById('ceramic-plus');
    const disp  = document.getElementById('ceramic-qty-display');
    const sumQty   = document.getElementById('ceramic-summary-qty');
    const sumTotal = document.getElementById('ceramic-summary-total');
    if (minus) minus.disabled = ceramicQty <= 1;
    if (plus)  plus.disabled  = ceramicQty >= 10;
    if (disp)  disp.textContent = ceramicQty;
    if (sumQty) sumQty.textContent = ceramicQty;
    if (sumTotal) sumTotal.textContent = 'PKR ' + total.toLocaleString();
}

// ── Reviews toggle ─────────────────────────────────────────────────────────
function toggleReviews(btn) {
    const body = document.getElementById('reviews-body');
    const chevron = btn.querySelector('i');
    const isOpen = btn.getAttribute('aria-expanded') === 'true';
    body.style.display = isOpen ? 'none' : 'block';
    btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

// Init disabled states + initial summary display
document.addEventListener('DOMContentLoaded', () => {
    const qm = document.getElementById('qty-minus');
    if (qm) qm.disabled = true;
    if (BALL_PACKS.length) selectBallsPack(0);
    // Set correct initial total for ceramic/gift-box
    const st = document.getElementById('ceramic-summary-total');
    if (st) st.textContent = 'PKR ' + CERAMIC_UNIT_PRICE.toLocaleString();
});

// ── Live updates from the admin (layouts/live-updates) ─────────────────────
// Prices, discounts, name and photo are patched in place, keeping what the
// shopper picked; if sizes / packs / pricing mode changed, the page re-renders.
window.LIVE_PRODUCT_IDS = <?php echo json_encode($livePageProduct ? [$livePageProduct->id] : [], 15, 512) ?>;
const LIVE_PAGE_PRODUCT_ID = <?php echo json_encode($livePageProduct?->id, 15, 512) ?>;
const LIVE_PAGE_STRUCTURE  = <?php echo json_encode($livePageStructure, 15, 512) ?>;

document.addEventListener('live:catalog', (e) => {
    if (!LIVE_PAGE_PRODUCT_ID) return;
    const product = e.detail.products[LIVE_PAGE_PRODUCT_ID];
    if (!product || product.structure !== LIVE_PAGE_STRUCTURE) {
        location.reload();
        return;
    }
    const fresh = (id) => {
        const v = product.variants[id] || {};
        return { price: v.price, original: v.original, percent: v.percent, preorder: v.preorder, available: v.available };
    };

    // Mystery Balls packs
    if (BALL_PACKS.length) {
        BALL_PACKS.forEach(pack => Object.assign(pack, fresh(pack.id)));
        document.querySelectorAll('.balls-pack-btn').forEach(btn => {
            const pack = BALL_PACKS[btn.dataset.idx];
            btn.querySelector('.size-price').innerHTML = livePriceHtml(pack.price, pack.original);
        });
        const first = BALL_PACKS[0], perFive = 5 / first.qty;
        document.querySelectorAll('.balls-base-price').forEach(el => el.innerHTML = livePriceHtml(first.price * perFive, first.original ? first.original * perFive : null));
        selectBallsPack(ballsIdx);
    }

    // Mystery Scoop sizes
    if (SCOOP_OPTIONS.length) {
        SCOOP_OPTIONS.forEach(option => {
            const { price, original, percent } = fresh(option.id);
            Object.assign(option, { price, original, percent });
        });
        document.querySelectorAll('[data-scoop-idx]').forEach(el => {
            const option = SCOOP_OPTIONS[el.dataset.scoopIdx];
            el.innerHTML = livePriceHtml(option.price, option.original);
        });
        const picked = SCOOP_OPTIONS[scoopIdx];
        const dp = document.getElementById('display-price');
        if (dp) dp.innerHTML = livePriceHtml(picked.price, picked.original);
        setDiscountBadge(picked.percent);
        updateScoopSummary();
    }

    if (typeof piApplyLive === 'function') piApplyLive(product);
});

// ── Product-Item (DB product) ───────────────────────────────────────────────
<?php if($productType === 'product-item'): ?>
// For balls, piUnitPrice is the selected pack's whole price and piQty its ball count.
let piUnitPrice     = <?php echo e($piInitialUnitPrice ?? $minPrice ?? 0); ?>;
let piOriginalPrice = <?php echo e($minOriginal ?? 'null'); ?>;   // regular price while on sale
let piVariantName = '<?php echo e(addslashes($firstVariant?->variant_name ?? '')); ?>';
let piVariantId   = <?php echo e($firstVariant?->id ?? 'null'); ?>;
let piQty         = <?php echo e(($isBalls ?? false) ? ($ballsMin ?? 5) : 1); ?>;
let piIsPreorder  = <?php echo e(($isPreorderItem ?? false) ? 'true' : 'false'); ?>;
const PI_IS_BALLS   = <?php echo e(($isBalls ?? false) ? 'true' : 'false'); ?>;
const PI_BALLS_AUTOMATIC = <?php echo e(($ballsMode ?? null) === \App\Models\Product::PRICING_AUTOMATIC ? 'true' : 'false'); ?>;
const PI_MAX_QTY    = <?php echo e($firstVariant?->max_qty ?? 10); ?>;
const PI_PRODUCT_ID = 'pi-<?php echo e($product->id ?? 'x'); ?>';
const PI_PRODUCT_NAME = '<?php echo e(addslashes($product->name ?? '')); ?>';
const PI_PRODUCT_IMG  = '<?php echo e($productImg ?? ''); ?>';
const PI_DB_PRODUCT_ID = <?php echo e($product->id ?? 'null'); ?>;
// Current prices per variant id — the variant buttons read these, and live updates refresh them
const PI_VARIANTS = <?php echo json_encode((object) ($piVariants ?? []), 15, 512) ?>;
const PI_HEADLINE_VARIANT_ID = <?php echo e($firstVariant?->id ?? 'null'); ?>;

function piSetPreorder(isPreorder) {
    piIsPreorder = !!isPreorder;
    const banner = document.getElementById('pi-preorder-banner');
    const badge  = document.getElementById('pi-preorder-badge');
    if (banner) banner.classList.toggle('d-flex', piIsPreorder);
    if (banner) banner.classList.toggle('d-none', !piIsPreorder);
    if (badge)  badge.style.display = piIsPreorder ? '' : 'none';
}

function piSelectVariant(btn, variantId) {
    const variant = PI_VARIANTS[variantId];
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    setDiscountBadge(variant.percent);
    piUnitPrice     = variant.price;
    piOriginalPrice = variant.original;
    piVariantName   = variant.name;
    piVariantId     = variantId;
    piQty = 1;
    document.getElementById('pi-qty-display').textContent = piQty;
    const minus = document.getElementById('pi-qty-minus');
    if (minus) minus.disabled = true;

    piSetPreorder(variant.preorder);
    piRefreshSummary();
}

// Fresh prices / name / photo from the admin (called by the live:catalog handler below)
function piApplyLive(product) {
    document.querySelectorAll('[data-pi-name]').forEach(el => el.textContent = product.name);
    document.title = product.name + ' | Ravellen';
    document.querySelectorAll('[data-pi-description]').forEach(el => el.textContent = product.description || '');

    const img = document.querySelector('.product-img');
    if (img && product.image && img.getAttribute('src') !== product.image) {
        img.src = product.image;
        img.parentElement.style.setProperty('--product-img', `url("${product.image}")`);
    }

    Object.entries(product.variants).forEach(([id, fresh]) => {
        if (!PI_VARIANTS[id]) return;
        Object.assign(PI_VARIANTS[id], { price: fresh.price, original: fresh.original, percent: fresh.percent, preorder: fresh.preorder });
        const price = document.querySelector(`.size-btn[data-variant-id="${id}"] .size-price`);
        if (price) price.innerHTML = livePriceHtml(fresh.price, fresh.original);
    });

    const headline = PI_VARIANTS[PI_HEADLINE_VARIANT_ID];
    const headlineEl = document.querySelector('[data-pi-headline]');
    if (headline && headlineEl) headlineEl.innerHTML = livePriceHtml(headline.price, headline.original);

    // balls are re-priced through selectBallsPack(); other products follow the picked variant
    const picked = PI_VARIANTS[piVariantId];
    if (!PI_IS_BALLS && picked) {
        piUnitPrice     = picked.price;
        piOriginalPrice = picked.original;
        setDiscountBadge(picked.percent);
        piSetPreorder(picked.preorder);
        piRefreshSummary();
    }
}

// Called by selectBallsPack() whenever the balls stepper / price list changes.
function piApplyBallsPack(pack) {
    piQty           = pack.qty;
    piUnitPrice     = pack.price;
    piOriginalPrice = pack.original;
    piVariantId     = pack.id;
    piVariantName   = 'Pack of ' + pack.qty;

    document.getElementById('pi-qty-display').textContent = piQty;
    document.getElementById('pi-qty-minus').disabled = ballsIdx <= 0;
    document.getElementById('pi-qty-plus').disabled  = ballsIdx >= BALL_PACKS.length - 1;
    document.getElementById('pi-add-btn').disabled   = !pack.available;

    piSetPreorder(pack.preorder);
    piRefreshSummary();
}

function piUpdateQty(change) {
    piQty = Math.min(PI_MAX_QTY, Math.max(1, piQty + change));
    document.getElementById('pi-qty-display').textContent = piQty;
    document.getElementById('pi-qty-minus').disabled = piQty <= 1;
    document.getElementById('pi-qty-plus').disabled  = piQty >= PI_MAX_QTY;
    piRefreshSummary();
}

function piRefreshSummary() {
    const lines         = PI_IS_BALLS ? 1 : piQty;   // a balls pack is one line at its whole price
    const total         = lines * piUnitPrice;
    const originalTotal = piOriginalPrice ? lines * piOriginalPrice : null;
    const advance       = Math.round(total * 0.4);
    // Automatic balls pricing quotes per 5 balls; everything else shows the line's unit price
    const unitScale     = PI_IS_BALLS && PI_BALLS_AUTOMATIC ? 5 / piQty : 1;

    const elVariant     = document.getElementById('pi-summary-variant');
    const elQty         = document.getElementById('pi-summary-qty');
    const elUnit        = document.getElementById('pi-summary-unit');
    const elTotalLabel  = document.getElementById('pi-summary-total-label');
    const elTotal       = document.getElementById('pi-summary-total');
    const elRemainBox   = document.getElementById('pi-remaining-note-box');
    const elSpacer      = document.getElementById('pi-spacer');
    const elBtnLabel    = document.getElementById('pi-btn-label');
    const elBtn         = document.getElementById('pi-btn-total');

    if (elVariant) elVariant.textContent = piVariantName;
    if (elQty)     elQty.textContent     = PI_IS_BALLS ? piQty + ' Balls' : piQty;
    if (elUnit)    elUnit.innerHTML      = priceHtml(piUnitPrice * unitScale, piOriginalPrice ? piOriginalPrice * unitScale : null);

    const displayPrice = document.getElementById('pi-display-price');
    if (displayPrice) displayPrice.innerHTML = priceHtml(piUnitPrice, piOriginalPrice);

    if (piIsPreorder) {
        if (elTotalLabel) elTotalLabel.textContent = 'Advance (40%)';
        if (elTotal)  elTotal.textContent  = fmtPkr(advance);
        if (elRemainBox) elRemainBox.style.display = '';
        if (elSpacer) elSpacer.style.display = 'none';
        if (elBtnLabel) elBtnLabel.textContent = 'Pre-order Now';
        if (elBtn)    elBtn.textContent    = fmtPkr(advance);
    } else {
        if (elTotalLabel) elTotalLabel.textContent = 'Total';
        if (elTotal) elTotal.innerHTML = priceHtml(total, originalTotal);
        if (elRemainBox) elRemainBox.style.display = 'none';
        if (elSpacer) elSpacer.style.display = '';
        if (elBtnLabel) elBtnLabel.textContent = 'Add to Cart';
        if (elBtn)   elBtn.textContent   = fmtPkr(total);
    }
}

function piAddToCart() {
    const subtitle  = piVariantName ? 'Variant: ' + piVariantName : null;
    const specials  = document.getElementById('pi-special-requests')?.value?.trim() || null;

    // Payload sent to the DB API (authenticated users). Balls variants are
    // priced per whole pack, so the selected pack is always qty 1 — piQty is
    // the ball count shown to the user, not how many pack-rows to buy.
    const dbPayload = {
        product_id:         PI_DB_PRODUCT_ID,
        product_variant_id: piVariantId,
        qty:                PI_IS_BALLS ? 1 : piQty,
        comment:            specials,
    };

    // localStorage shape (works for guests + gives instant UI feedback) —
    // same line shape as the DB cart: one balls pack = qty 1 at the pack price
    const localItem = {
        id:        PI_PRODUCT_ID,
        name:      PI_PRODUCT_NAME,
        subtitle:  subtitle,
        image:     PI_PRODUCT_IMG,
        unitPrice: piUnitPrice,
        originalPrice: piOriginalPrice,
        qty:       PI_IS_BALLS ? 1 : piQty,
        preorder:  piIsPreorder,
        comment:   specials,
    };

    persistToCart(dbPayload, localItem);
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const minus = document.getElementById('pi-qty-minus');
    if (minus) minus.disabled = true;
    piRefreshSummary();
});
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/product-detail.blade.php ENDPATH**/ ?>