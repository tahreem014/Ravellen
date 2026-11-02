<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // One row per storefront visit and one per click, for the admin dashboard.
        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);      // 'visit' | 'click'
            $table->string('visit_id', 32);  // the visit this event belongs to (cookie value)
            $table->timestamp('created_at')->nullable();

            $table->index(['type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_events');
    }
};
