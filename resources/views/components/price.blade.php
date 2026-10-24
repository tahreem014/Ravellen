@props(['variant' => null, 'amount' => null, 'original' => null, 'prefix' => '', 'empty' => 'PKR —'])
@php
    // Pass a ProductVariant, or a raw amount plus the regular price when it's discounted.
    if ($variant) {
        $amount   = $variant->effectivePrice();
        $original = $variant->hasDiscount() ? (float) $variant->price : null;
    }
@endphp
@if ($amount === null){{ $empty }}@else{{ $prefix }}PKR {{ number_format($amount) }}@if ($original !== null && $original > $amount) <s class="price-was" style="color:#9ca3af;-webkit-text-fill-color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">PKR {{ number_format($original) }}</s>@endif
@endif
