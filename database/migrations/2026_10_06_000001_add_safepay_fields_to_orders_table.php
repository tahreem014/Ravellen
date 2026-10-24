<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tracker reference for the initial charge (full payment for a
            // normal order, or the advance for a preorder).
            $table->string('payment_reference')->nullable()->after('payment_stage');

            // Tracker reference for the preorder's separate remaining-balance charge.
            $table->string('remaining_payment_reference')->nullable()->after('payment_reference');

            // When the initial charge above was confirmed paid (pairs with
            // the existing remaining_paid_at for the second charge).
            $table->timestamp('paid_at')->nullable()->after('remaining_payment_reference');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'remaining_payment_reference', 'paid_at']);
        });
    }
};
