{{--
    Mystery Balls price block. Automatic pricing quotes one price per 5 balls
    (every pack is a multiple of it); custom pricing lists each quantity with
    its own price, and clicking one selects that quantity.
    $packs: Product::packOptions()   $mode: the product's pricing_mode   $accent: theme colour
--}}
@php
    $firstPack = $packs[0] ?? null;
    $perFive   = $firstPack ? \App\Models\Product::PACK_STEP / $firstPack['qty'] : 1;
@endphp
@if(! $firstPack)
    <div class="text-muted">This product is currently unavailable.</div>
@elseif($mode === \App\Models\Product::PRICING_AUTOMATIC)
    <div class="text-muted small mb-1">Price per 5 balls</div>
    <div class="fw-bold balls-base-price" style="font-size:1.75rem;color:{{ $accent }};">
        <x-price :amount="$firstPack['price'] * $perFive" :original="$firstPack['original'] !== null ? $firstPack['original'] * $perFive : null" />
    </div>
    <div class="text-muted" style="font-size:.78rem;">Every extra 5 balls adds the same amount.</div>
@else
    <div class="text-muted small mb-1">Price for <span class="balls-price-qty">{{ $firstPack['qty'] }}</span> balls</div>
    <div class="fw-bold balls-price" style="font-size:1.75rem;color:{{ $accent }};">
        <x-price :amount="$firstPack['price']" :original="$firstPack['original']" />
    </div>
    <div class="d-flex flex-wrap gap-2 mt-2">
        @foreach($packs as $i => $pack)
            <button type="button" class="size-btn balls-pack-btn {{ $i === 0 ? 'active' : '' }}" data-idx="{{ $i }}" onclick="selectBallsPack({{ $i }})">
                <div class="size-name">{{ $pack['qty'] }} balls</div>
                <div class="size-price"><x-price :amount="$pack['price']" :original="$pack['original']" /></div>
            </button>
        @endforeach
    </div>
@endif
