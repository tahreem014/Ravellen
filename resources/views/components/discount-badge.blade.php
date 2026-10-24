@props(['label' => null, 'id' => null, 'live' => false])
{{--
    "-15%" sale badge for the bottom-left corner of a product image (the other
    corners hold the sub-category label and the heart / pre-order badge).
    The image wrapper must be position: relative. With an id or live, it is
    always rendered — hidden while empty — so it can appear later: an id is
    for the product page's own script (badge follows the picked variant),
    live for listing cards updated by layouts/live-updates.
--}}
@if ($label || $id || $live)
    <span @if ($id) id="{{ $id }}" @endif class="discount-badge" @if ($live) data-live-badge @endif
          style="position:absolute;left:.6rem;bottom:.6rem;z-index:2;background:#e11d48;color:#fff;font-weight:700;font-size:.72rem;line-height:1;padding:.35rem .6rem;border-radius:2rem;box-shadow:0 2px 6px rgba(0,0,0,.2);letter-spacing:.02em;{{ $label ? '' : 'display:none;' }}">{{ $label }}</span>
@endif
