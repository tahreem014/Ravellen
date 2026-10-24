<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPricingModeToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('pricing_mode')->nullable()->after('description');
        });

        // Mystery Balls products already sell one "Pack of N" variant per
        // quantity, each with its own hand-set price — that is custom pricing,
        // so they start there and none of their prices change.
        $packProductIds = DB::table('product_variants')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw("SUM(CASE WHEN variant_name LIKE 'Pack of %' THEN 0 ELSE 1 END) = 0")
            ->pluck('product_id');

        DB::table('products')->whereIn('id', $packProductIds)->update(['pricing_mode' => 'custom']);
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('pricing_mode');
        });
    }
}
