<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'support_email', 'value' => 'info@ravellen.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_phone', 'value' => '+92-300-1234567', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'preorder_advance_rate', 'value' => '0.4', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
