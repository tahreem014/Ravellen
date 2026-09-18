<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPreOrdersTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('pre_orders');
    }

    public function down()
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('price', 10, 2);
            $table->decimal('advanced_payment', 10, 2);
            $table->decimal('remaining_payment', 10, 2);
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
    }
}
