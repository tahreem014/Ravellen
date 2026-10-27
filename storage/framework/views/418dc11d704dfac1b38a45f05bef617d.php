<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Coming Soon', 'message' => null, 'icon' => 'ri-cup-line', 'accent' => '#8B5E3C', 'accent2' => '#C8A98A', 'actions' => true]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => 'Coming Soon', 'message' => null, 'icon' => 'ri-cup-line', 'accent' => '#8B5E3C', 'accent2' => '#C8A98A', 'actions' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'coming-soon text-center mx-auto'])); ?> style="max-width:34rem;padding:2.5rem 1.25rem;">
    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
         style="width:4.5rem;height:4.5rem;border-radius:50%;color:#fff;font-size:2rem;background:linear-gradient(135deg,<?php echo e($accent2); ?>,<?php echo e($accent); ?>);box-shadow:0 8px 24px rgba(139,94,60,.25);">
        <i class="<?php echo e($icon); ?>"></i>
    </div>
    <span class="d-inline-block mb-3"
          style="background:#fff;color:<?php echo e($accent); ?>;border:1.5px solid <?php echo e($accent2); ?>;border-radius:2rem;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .9rem;">Coming Soon</span>
    <h3 class="coming-soon-title fw-bold mb-2" style="color:#5c3d1e;font-size:clamp(1.4rem,4vw,1.9rem);"><?php echo e($title); ?></h3>
    <?php if($message): ?>
        <p class="text-muted mb-4" style="font-size:.95rem;"><?php echo e($message); ?></p>
    <?php endif; ?>
    <?php if($actions): ?>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?php echo e(url('/shop')); ?>" class="btn fw-semibold text-white px-4" style="background:linear-gradient(135deg,<?php echo e($accent2); ?>,<?php echo e($accent); ?>);border-radius:.5rem;">Browse the Shop</a>
            <a href="<?php echo e(url('/')); ?>" class="btn fw-semibold px-4" style="background:#fff;color:<?php echo e($accent); ?>;border:1.5px solid <?php echo e($accent2); ?>;border-radius:.5rem;">Back to Home</a>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/components/coming-soon.blade.php ENDPATH**/ ?>