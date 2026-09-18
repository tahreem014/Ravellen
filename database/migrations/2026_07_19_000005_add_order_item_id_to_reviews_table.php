<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderItemIdToReviewsTable extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_item_id')->after('product_id')->constrained('order_items')->cascadeOnDelete();
            $table->unique('order_item_id');
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['order_item_id']);
            $table->dropConstrainedForeignId('order_item_id');
        });
    }
}
