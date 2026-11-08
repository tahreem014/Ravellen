{{--
    "Only 12 left" note for the product page: shown while the picked variant / pack / size is down to its
    last few units (ProductVariant::LOW_STOCK_THRESHOLD), with a bar that shrinks as the number drops.
    It starts hidden — pages/product-detail's script (renderStockLeft) fills it in for whatever is picked
    and keeps the number counting down as orders come in. Its styles live in that page's css section.
--}}
<div class="stock-left mb-3" data-stock-left hidden role="status" aria-live="polite">
    <div class="stock-left-text">
        <i class="ri-fire-line"></i>
        <span>Only <strong class="stock-left-count" data-stock-left-count>0</strong> left in stock</span>
    </div>
    <div class="stock-left-bar" aria-hidden="true"><span data-stock-left-fill></span></div>
</div>
