<?php $__env->startSection('title', $name); ?>

<?php $__env->startSection('content'); ?>
<?php
    [$accent, $accent2] = match (true) {
        str_starts_with($name, 'BTS') => ['#7c3aed', '#a855f7'],
        $name === 'Ceramic Items'     => ['#8B5E3C', '#C8A98A'],
        default                       => ['#ec4899', '#f472b6'],
    };
?>

<div class="pb-5 rv-pt-back" style="min-height:70vh;"
     data-live-listing="category:<?php echo e($name); ?>"
     data-live-signature="<?php echo e(app(\App\Services\LiveUpdateService::class)->listingSignature('category:' . $name)); ?>">
    <div class="container px-3 px-sm-4 pt-5">
        <?php if (isset($component)) { $__componentOriginala96be7eb5a7f8a23476ff5e6addf7d37 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala96be7eb5a7f8a23476ff5e6addf7d37 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.coming-soon','data' => ['title' => 'We\'re getting ' . $name . ' ready','message' => 'Check back soon!','accent' => $accent,'accent2' => $accent2]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('coming-soon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('We\'re getting ' . $name . ' ready'),'message' => 'Check back soon!','accent' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent),'accent2' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accent2)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala96be7eb5a7f8a23476ff5e6addf7d37)): ?>
<?php $attributes = $__attributesOriginala96be7eb5a7f8a23476ff5e6addf7d37; ?>
<?php unset($__attributesOriginala96be7eb5a7f8a23476ff5e6addf7d37); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala96be7eb5a7f8a23476ff5e6addf7d37)): ?>
<?php $component = $__componentOriginala96be7eb5a7f8a23476ff5e6addf7d37; ?>
<?php unset($__componentOriginala96be7eb5a7f8a23476ff5e6addf7d37); ?>
<?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/coming-soon.blade.php ENDPATH**/ ?>