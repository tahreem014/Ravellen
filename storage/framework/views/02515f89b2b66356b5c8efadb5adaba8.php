<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label' => null, 'id' => null, 'live' => false]));

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

foreach (array_filter((['label' => null, 'id' => null, 'live' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($label || $id || $live): ?>
    <span <?php if($id): ?> id="<?php echo e($id); ?>" <?php endif; ?> class="discount-badge" <?php if($live): ?> data-live-badge <?php endif; ?>
          style="position:absolute;left:.6rem;bottom:.6rem;z-index:2;background:#e11d48;color:#fff;font-weight:700;font-size:.72rem;line-height:1;padding:.35rem .6rem;border-radius:2rem;box-shadow:0 2px 6px rgba(0,0,0,.2);letter-spacing:.02em;<?php echo e($label ? '' : 'display:none;'); ?>"><?php echo e($label); ?></span>
<?php endif; ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/components/discount-badge.blade.php ENDPATH**/ ?>