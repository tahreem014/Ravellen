<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCommentPreferenceFromOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['comment', 'preference']);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('comment')->nullable();
            $table->string('preference')->nullable();
        });
    }
}
