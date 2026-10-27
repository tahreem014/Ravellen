
<?php $reviewedProduct = $product ?? $genericProduct ?? null; ?>
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
            <p class="text-muted small mb-0">No reviews yet.<?php echo e($reviewedProduct ? ' Be the first to review this product!' : ''); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/partials/product-reviews.blade.php ENDPATH**/ ?>