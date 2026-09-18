<div class="product-row d-flex gap-3 align-items-start">
    <img src="{{ $product->images->first()?->image_url ?? 'https://via.placeholder.com/100?text=No+Image' }}"
         alt="{{ $product->name }}" class="product-thumb">

    <div class="flex-grow-1">
        <div class="fw-bold">{{ $product->name }}</div>
        <div class="text-muted small mb-2">{{ $product->description }}</div>

        @forelse($product->variants as $variant)
            <span class="variant-badge">
                {{ $variant->variant_name }} — PKR {{ number_format($variant->price, 0) }}
            </span>
        @empty
            <span class="text-muted small">No variants.</span>
        @endforelse
    </div>
</div>
