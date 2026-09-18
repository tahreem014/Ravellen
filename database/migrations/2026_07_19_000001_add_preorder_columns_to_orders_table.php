<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreorderColumnsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type', 20)->default('normal')->after('user_id');
            $table->string('payment_stage', 20)->nullable()->after('payment_status');
            $table->decimal('advance_amount', 10, 2)->nullable()->after('total_amount');
            $table->decimal('remaining_amount', 10, 2)->nullable()->after('advance_amount');
            $table->timestamp('remaining_paid_at')->nullable()->after('delivered_at');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'payment_stage', 'advance_amount', 'remaining_amount', 'remaining_paid_at']);
        });
    }
}
