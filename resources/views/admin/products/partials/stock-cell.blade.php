{{--
    Stock of one product on the admin product list: one badge with the units shoppers can still order across
    its variants (green plenty, amber 15 or fewer, red out of stock). Rendered here for the page and again by
    Admin\ProductStockController for every live poll, so what the admin sees is always what the server says.
    Styles: products/index. Each variant's own figure is on the product's edit page.

    $product: the product, with its variants loaded
--}}
@php
    use App\Models\ProductVariant;

    $total = $product->stockTotal();

    // plenty / low / out / not limited by stock (pre-order only)
    if ($total > 0) {
        [$tone, $text] = [$total <= ProductVariant::LOW_STOCK_THRESHOLD ? 'low' : 'ok', number_format($total) . ' in stock'];
    } elseif ($label = $product->stockLabel()) {
        [$tone, $text] = ['out', $label];
    } else {
        [$tone, $text] = ['none', 'Pre-order'];
    }
@endphp
@if ($product->variants->isEmpty())
    <span class="text-muted">&mdash;</span>
@else
    <span class="stock-total stock-{{ $tone }}">{{ $text }}</span>
@endif
