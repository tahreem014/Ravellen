{{--
    The "Reviews" card on every product page. The reviews are the database's own — a product's reviews
    are the ones left on its variants. The page supplies $productReviews (newest first), $reviewCount and
    $avgRating: the product page from HomeController::productDetail, and the Mystery Scoop / Mystery Balls
    / BTS pages from the product behind them (HomeController::genericProductPage).
--}}
@php $reviewedProduct = $product ?? $genericProduct ?? null; @endphp
<div class="card border-0 shadow-sm rounded-3 p-4">
    <button class="d-flex justify-content-between align-items-center w-100 bg-transparent border-0 p-0 mb-3"
            onclick="toggleReviews(this)" aria-expanded="true">
        <h5 class="fw-bold mb-0">Reviews</h5>
        <i class="ri-arrow-down-s-line" style="font-size:1.25rem;color:#6b7280;transition:transform .3s;display:inline-block;transform:rotate(180deg);"></i>
    </button>
    <div id="reviews-body">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="fw-bold fs-5">{{ number_format($avgRating, 1) }}</span>
            <div>
                @for($s=1;$s<=5;$s++)<i class="ri-star-{{ $s<=round($avgRating)?'fill':'line' }} star"></i>@endfor
            </div>
            <span class="text-muted small">({{ $reviewCount }} review{{ $reviewCount === 1 ? '' : 's' }})</span>
        </div>
        @forelse($productReviews as $review)
            <div class="review-item">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold small">{{ $review->user->name ?? 'Anonymous' }}</span>
                    <span class="text-muted" style="font-size:.75rem;">{{ $review->created_at->format('d M Y') }}</span>
                </div>
                <div class="mb-2">
                    @for($s=1;$s<=5;$s++)<i class="ri-star-{{ $s<=$review->rating?'fill':'line' }} star"></i>@endfor
                </div>
                @if($review->comments)
                <p class="text-muted small mb-2">{{ $review->comments }}</p>
                @endif
                @if($review->images->isNotEmpty())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($review->images as $reviewImage)
                    <img src="{{ asset('images/reviews/' . $reviewImage->image_url) }}"
                         alt="Review image" class="review-img">
                    @endforeach
                </div>
                @endif
            </div>
        @empty
            <p class="text-muted small mb-0">No reviews yet.{{ $reviewedProduct ? ' Be the first to review this product!' : '' }}</p>
        @endforelse
    </div>
</div>
