@props(['label' => null, 'live' => false])
{{--
    "Out of stock" / "Restocking" badge for the bottom-right corner of a product image
    (the discount badge holds the bottom-left). The image wrapper must be position: relative.
    With live it is always rendered — hidden while empty — so layouts/live-updates can show
    it when the admin changes a status.
--}}
@if ($label || $live)
    <span class="stock-badge" @if ($live) data-live-stock @endif
          style="position:absolute;right:.6rem;bottom:.6rem;z-index:2;background:#374151;color:#fff;font-weight:700;font-size:.72rem;line-height:1;padding:.35rem .6rem;border-radius:2rem;box-shadow:0 2px 6px rgba(0,0,0,.2);letter-spacing:.02em;{{ $label ? '' : 'display:none;' }}">{{ $label }}</span>
@endif
