<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->string('shipping_address');
            $table->decimal('total_amount', 10, 2);
            $table->text('comment')->nullable();
            $table->string('preference')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->string('order_status')->default('pending');
            $table->string('payment_method');
            $table->string('payment_status')->default('pending');
            $table->string('tracking_number')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
