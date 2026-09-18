<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceProductIdWithProductVariantIdInReviewsTable extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('order_item_id')->constrained('product_variants')->cascadeOnDelete();
        });

        DB::statement('
            UPDATE reviews
            INNER JOIN order_items ON reviews.order_item_id = order_items.id
            SET reviews.product_variant_id = order_items.product_variant_id
        ');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('image');
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('order_item_id')->constrained('products')->cascadeOnDelete();
            $table->string('image')->nullable();
        });

        DB::statement('
            UPDATE reviews
            INNER JOIN product_variants ON reviews.product_variant_id = product_variants.id
            SET reviews.product_id = product_variants.product_id
        ');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
}
