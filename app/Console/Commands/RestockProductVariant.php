<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Console\Command;

class RestockProductVariant extends Command
{
    /**
     * php artisan variant:restock 42
     * php artisan variant:restock 42 --status=out_of_stock
     */
    protected $signature = 'variant:restock
        {variant_id : The product_variants.id to update}
        {--status=active : The new status (active, out_of_stock, restocking, pre_order, discontinued)}';

    protected $description = 'Update a product variant\'s status through Eloquent so restock/awaiting-stock notifications fire correctly';

    public function handle(): int
    {
        $variant = ProductVariant::with('product')->find($this->argument('variant_id'));

        if (! $variant) {
            $this->error("No product variant found with id {$this->argument('variant_id')}.");

            return self::FAILURE;
        }

        $status = $this->option('status');
        $validStatuses = [
            ProductVariant::STATUS_ACTIVE,
            ProductVariant::STATUS_OUT_OF_STOCK,
            ProductVariant::STATUS_PRE_ORDER,
            ProductVariant::STATUS_RESTOCKING,
            ProductVariant::STATUS_DISCONTINUED,
        ];

        if (! in_array($status, $validStatuses, true)) {
            $this->error("Invalid status '{$status}'. Valid values: " . implode(', ', $validStatuses));

            return self::FAILURE;
        }

        $previousStatus = $variant->status;

        if ($previousStatus === $status) {
            $this->info("Variant #{$variant->id} ({$variant->product->name}) is already '{$status}' — nothing to do.");

            return self::SUCCESS;
        }

        $awaitingOrderCount = $status === ProductVariant::STATUS_ACTIVE
            ? Order::awaitingStock()->whereHas('items', fn ($q) => $q->where('product_variant_id', $variant->id))->count()
            : 0;
        $wishlistCount = $status === ProductVariant::STATUS_ACTIVE
            ? $variant->product->wishLists()->count()
            : 0;

        $variant->update(['status' => $status]);

        $this->info("Variant #{$variant->id} ({$variant->product->name}) changed from '{$previousStatus}' to '{$status}'.");

        if ($status === ProductVariant::STATUS_ACTIVE) {
            $this->info("Notified {$awaitingOrderCount} awaiting-stock order(s) and {$wishlistCount} wishlisted user(s).");
        }

        return self::SUCCESS;
    }
}
