<?php

use App\Models\ProductVariant;
use App\Services\LiveUpdateService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStockTrackingToOrderItemsTable extends Migration
{
    public function up()
    {
        // How many units of its variant this line is holding out of the stock:
        //   NULL  never held any (orders placed before stock was tracked, and pre-orders)
        //   n > 0 held until the order is cancelled, which gives them back
        //   0     held once and already given back
        // Cancelling an old order must not add stock that was never taken, hence NULL rather than 0.
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('stock_reserved')->nullable()->after('qty');
        });

        // Stock now decides availability: a variant with nothing left is out of stock. Variants
        // that were saved as active with a stock of 0 before stock was tracked are brought in line.
        $moved = DB::table('product_variants')
            ->where('status', ProductVariant::STATUS_ACTIVE)
            ->where('stock', '<=', 0)
            ->update(['status' => ProductVariant::STATUS_OUT_OF_STOCK, 'updated_at' => now()]);

        if ($moved > 0) {
            app(LiveUpdateService::class)->bump();
        }
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('stock_reserved');
        });
    }
}
