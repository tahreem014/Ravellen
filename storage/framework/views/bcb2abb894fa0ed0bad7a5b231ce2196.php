<?php $__env->startSection('title', 'My Wishlist'); ?>

<?php $__env->startSection('css'); ?>
<style>
    .wl-wrapper {
        background: linear-gradient(to bottom, #fdf2f8, #f5f3ff);
        min-height: 100vh;
        padding: 3rem 1rem;
    }

    /* ── Empty state ── */
    .wl-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
    }
    .wl-empty-icon {
        width: 6rem;
        height: 6rem;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 24px rgba(168,85,247,.12);
        margin-bottom: 1.75rem;
    }
    .wl-empty-icon i {
        font-size: 2.5rem;
        color: #d1d5db;
    }
    .wl-empty h2 {
        font-weight: 800;
        font-size: 1.75rem;
        color: #111827;
        margin-bottom: .5rem;
    }
    .wl-empty p {
        color: #6b7280;
        font-size: .95rem;
        margin-bottom: 1.75rem;
    }
    .wl-btn-shop {
        background: linear-gradient(to right, #ec4899, #a855f7);
        color: #fff;
        border: none;
        border-radius: 2rem;
        padding: .7rem 2rem;
        font-weight: 600;
        font-size: .95rem;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        text-decoration: none;
        transition: opacity .2s;
    }
    .wl-btn-shop:hover { opacity: .88; color: #fff; }

    /* ── Heading ── */
    .wl-heading {
        font-weight: 800;
        font-size: clamp(2rem, 5vw, 3rem);
        background: linear-gradient(to right, #ec4899, #a855f7);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: .25rem;
    }
    .wl-subheading {
        color: #6b7280;
        font-size: .95rem;
        margin-bottom: 2rem;
    }

    /* ── Item card (horizontal row) ── */
    .wl-card {
        background: #fff;
        border-radius: .5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        overflow: hidden;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        transition: box-shadow .25s;
    }
    .wl-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.12); }

    .wl-card-img-wrap {
        position: relative;
        width: 9rem; min-width: 9rem; height: 9rem;
        overflow: hidden; flex-shrink: 0;
    }
    .wl-card-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .4s;
    }
    .wl-card:hover .wl-card-img-wrap img { transform: scale(1.06); }

    .wl-card-body {
        flex: 1; padding: 1rem 1.25rem;
        display: flex; flex-direction: column; justify-content: center;
    }
    .wl-item-name  { font-weight: 700; font-size: .95rem; color: #111827; margin-bottom: .2rem; }
    .wl-item-desc  { font-size: .78rem; color: #6b7280; margin-bottom: .4rem; }
    .wl-item-price {
        font-weight: 700; font-size: .9rem;
        background: linear-gradient(to right, #ec4899, #a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: .65rem;
    }
    .wl-view-btn {
        border: 0; color: #fff; font-weight: 600; font-size: .8rem;
        border-radius: .45rem; padding: .4rem .9rem;
        background: linear-gradient(135deg,#f472b6,#ec4899);
        transition: opacity .2s; display: inline-flex; align-items: center;
        gap: .3rem; text-decoration: none; align-self: flex-start;
    }
    .wl-view-btn:hover { opacity: .85; color: #fff; }

    .wl-card-actions {
        display: flex; align-items: center; justify-content: center;
        padding: 0 1rem; flex-shrink: 0;
    }
    .wl-remove-btn {
        background: none; border: none; color: #ef4444;
        font-size: 1.2rem; cursor: pointer; padding: .4rem;
        border-radius: .4rem; transition: background .2s, transform .2s;
        display: flex; align-items: center; justify-content: center;
    }
    .wl-remove-btn:hover { background: #fef2f2; transform: scale(1.15); }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="wl-wrapper">
    <div class="container px-4" style="max-width:52rem;">

        <?php if(empty($wishlist)): ?>
            
            <div class="wl-empty">
                <div class="wl-empty-icon">
                    <i class="ri-heart-line"></i>
                </div>
                <h2>Your Wishlist is Empty</h2>
                <p>Save your favorite items for later!</p>
                <a href="<?php echo e(route('shop')); ?>" class="wl-btn-shop">
                    <i class="ri-shopping-bag-line"></i> Start Shopping
                </a>
            </div>

        <?php else: ?>
            
            <h1 class="wl-heading">My Wishlist</h1>
            <p class="wl-subheading"><?php echo e(count($wishlist)); ?> <?php echo e(count($wishlist) === 1 ? 'item' : 'items'); ?> saved</p>

            <?php
                $urlMap = [
                    'mystery-scoops' => '/mystery-scoops',
                    'mystery-balls'  => '/mystery-balls',
                    'bts-merch'      => '/bts-merch',
                    'gift-box'       => '/gift-box',
                    'gift-box-2'     => '/gift-box',
                    'gift-box-3'     => '/gift-box',
                    'ceramic-mug'    => '/ceramic-mug',
                    'ceramic-mug-2'  => '/ceramic-mug',
                    'ceramic-mug-3'  => '/ceramic-mug',
                ];
                $descMap = [
                    'mystery-scoops' => 'Curated mystery items in 3 sizes',
                    'mystery-balls'  => 'Fun mystery balls, minimum 5 pcs',
                    'bts-merch'      => 'Exclusive ARMY collection for all members',
                    'gift-box'       => 'Themed gift boxes for Tata lovers',
                    'gift-box-2'     => 'Themed gift boxes for Tata lovers',
                    'gift-box-3'     => 'Themed gift boxes for Tata lovers',
                    'ceramic-mug'    => 'Cute ceramic mugs, high-grade quality',
                    'ceramic-mug-2'  => 'Cute ceramic mugs, high-grade quality',
                    'ceramic-mug-3'  => 'Cute ceramic mugs, high-grade quality',
                ];

                $wishlistProductIds = collect($wishlist)->keys()
                    ->filter(fn($id) => preg_match('/^p-(\d+)$/', $id))
                    ->map(fn($id) => (int) substr($id, 2));
                $wishlistProducts = \App\Models\Product::with('variants')->whereIn('id', $wishlistProductIds)->get()->keyBy('id');
                $productSlugs = $wishlistProducts->pluck('slug', 'id');
            ?>

            <div class="d-flex flex-column gap-3" id="wl-items-container">
                <?php $__currentLoopData = $wishlist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $desc = $descMap[$item['id']] ?? '';
                    $url  = preg_match('/^p-(\d+)$/', $item['id'], $m)
                        ? route('product-item', $productSlugs[(int) $m[1]] ?? $m[1])
                        : url($urlMap[$item['id']] ?? '/shop');
                    // DB products show their live price (sales included), not the label saved when hearted
                    $liveProduct  = isset($m[1]) ? $wishlistProducts->get((int) $m[1]) : null;
                    $liveCheapest = $liveProduct?->cheapestVariant();
                ?>
                <div id="wl-item-<?php echo e($item['id']); ?>" <?php if($liveProduct): ?> data-live-product="<?php echo e($liveProduct->id); ?>" <?php endif; ?>>
                    <div class="wl-card">
                        <div class="wl-card-img-wrap">
                            <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" <?php if($liveProduct): ?> data-live-image <?php endif; ?>>
                            <?php if (isset($component)) { $__componentOriginalc259565ab925afbe6d933017bcb8ab06 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc259565ab925afbe6d933017bcb8ab06 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.discount-badge','data' => ['label' => $liveProduct?->discountLabel(),'live' => (bool) $liveProduct]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('discount-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($liveProduct?->discountLabel()),'live' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute((bool) $liveProduct)]); ?>
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
                        <div class="wl-card-body">
                            <div class="wl-item-name" data-live-name><?php echo e($item['name']); ?></div>
                            
                            <div class="wl-item-price" data-live-price data-live-prefix="From "><?php if($liveCheapest): ?><?php if (isset($component)) { $__componentOriginal5c7c50258000edf57abfef324d310474 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c7c50258000edf57abfef324d310474 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.price','data' => ['variant' => $liveCheapest,'prefix' => 'From ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('price'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($liveCheapest),'prefix' => 'From ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $attributes = $__attributesOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__attributesOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c7c50258000edf57abfef324d310474)): ?>
<?php $component = $__componentOriginal5c7c50258000edf57abfef324d310474; ?>
<?php unset($__componentOriginal5c7c50258000edf57abfef324d310474); ?>
<?php endif; ?><?php else: ?><?php echo e($item['price']); ?><?php endif; ?></div>
                            <a href="<?php echo e($url); ?>" class="wl-view-btn">
                                View Product <i class="ri-arrow-right-line"></i>
                            </a>
                        </div>
                        <div class="wl-card-actions">
                            <button class="wl-remove-btn"
                                    onclick="removeFromWishlist('<?php echo e($item['id']); ?>')"
                                    title="Remove from wishlist">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div id="wl-empty-fallback" style="display:none;min-height:60vh;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
                <div class="wl-empty-icon">
                    <i class="ri-heart-line"></i>
                </div>
                <h2 style="font-weight:800;font-size:1.75rem;color:#111827;margin-bottom:.5rem;">Your Wishlist is Empty</h2>
                <p style="color:#6b7280;font-size:.95rem;margin-bottom:1.75rem;">Save your favorite items for later!</p>
                <a href="<?php echo e(route('shop')); ?>" class="wl-btn-shop">
                    <i class="ri-shopping-bag-line"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
function removeFromWishlist(id) {
    fetch('<?php echo e(route('wish-list.remove')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({ id })
    })
    .then(r => r.json())
    .then((data) => {
        const el = document.getElementById('wl-item-' + id);
        if (el) el.remove();

        // sync heart on shop page if open in same tab (localStorage flag)
        const saved = JSON.parse(localStorage.getItem('rv_wishlist') || '{}');
        delete saved[id];
        localStorage.setItem('rv_wishlist', JSON.stringify(saved));

        if (typeof updateWishlistBadge === 'function') updateWishlistBadge(data.count);

        // show empty state if no items left
        const container = document.getElementById('wl-items-container');
        if (container && container.querySelectorAll('[id^="wl-item-"]').length === 0) {
            container.style.display = 'none';
            document.getElementById('wl-empty-fallback').style.display = 'flex';
            const h = document.querySelector('.wl-heading');
            const s = document.querySelector('.wl-subheading');
            if (h) h.style.display = 'none';
            if (s) s.style.display = 'none';
        }
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/wish-list.blade.php ENDPATH**/ ?>