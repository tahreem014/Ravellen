
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['selected' => null, 'placeholder' => null, 'pin' => ['PK']]));

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

foreach (array_filter((['selected' => null, 'placeholder' => null, 'pin' => ['PK']]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    $locations = app(\App\Services\LocationService::class);
    $current   = $locations->findCountry($selected)['name'] ?? null;
    $countries = collect($locations->countries());
    $pinned    = $countries->filter(fn ($country) => in_array($country['code'], $pin, true));
    $others    = $countries->reject(fn ($country) => in_array($country['code'], $pin, true));
?>
<?php if($placeholder): ?>
    <option value="" <?php if($current === null): echo 'selected'; endif; ?>><?php echo e($placeholder); ?></option>
<?php endif; ?>
<?php $__currentLoopData = $pinned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($country['name']); ?>" data-code="<?php echo e($country['code']); ?>" <?php if($current === $country['name']): echo 'selected'; endif; ?>><?php echo e($country['name']); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($pinned->isNotEmpty()): ?>
    <option disabled>──────────</option>
<?php endif; ?>
<?php $__currentLoopData = $others; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($country['name']); ?>" data-code="<?php echo e($country['code']); ?>" <?php if($current === $country['name']): echo 'selected'; endif; ?>><?php echo e($country['name']); ?></option>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/components/country-options.blade.php ENDPATH**/ ?>