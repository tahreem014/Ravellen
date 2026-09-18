<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveDefaultShippingFeeSetting extends Migration
{
    public function up()
    {
        DB::table('settings')->where('key', 'default_shipping_fee')->delete();
    }

    public function down()
    {
        DB::table('settings')->insert([
            'key' => 'default_shipping_fee',
            'value' => '200',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
