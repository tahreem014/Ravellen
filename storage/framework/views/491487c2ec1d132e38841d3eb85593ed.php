
<?php
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
?>
<?php if($product->variants->isEmpty()): ?>
    <span class="text-muted">&mdash;</span>
<?php else: ?>
    <span class="stock-total stock-<?php echo e($tone); ?>"><?php echo e($text); ?></span>
<?php endif; ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/partials/stock-cell.blade.php ENDPATH**/ ?>