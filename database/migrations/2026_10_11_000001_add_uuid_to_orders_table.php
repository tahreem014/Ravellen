<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Public, unguessable identifier used in every customer-facing
            // URL (/orders/{uuid}) so the sequential primary key is never
            // exposed or enumerable.
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table('orders')->whereNull('uuid')->orderBy('id')->each(function ($order) {
            DB::table('orders')->where('id', $order->id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
