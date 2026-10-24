<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => null, 'amount' => null, 'original' => null, 'prefix' => '', 'empty' => 'PKR —']));

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

foreach (array_filter((['variant' => null, 'amount' => null, 'original' => null, 'prefix' => '', 'empty' => 'PKR —']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    // Pass a ProductVariant, or a raw amount plus the regular price when it's discounted.
    if ($variant) {
        $amount   = $variant->effectivePrice();
        $original = $variant->hasDiscount() ? (float) $variant->price : null;
    }
?>
<?php if($amount === null): ?><?php echo e($empty); ?><?php else: ?><?php echo e($prefix); ?>PKR <?php echo e(number_format($amount)); ?><?php if($original !== null && $original > $amount): ?> <s class="price-was" style="color:#9ca3af;-webkit-text-fill-color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">PKR <?php echo e(number_format($original)); ?></s><?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/components/price.blade.php ENDPATH**/ ?>