<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class StandardizePreOrderStatusInProductVariantsTable extends Migration
{
    public function up()
    {
        DB::table('product_variants')->where('status', 'pre-order')->update(['status' => 'pre_order']);
    }

    public function down()
    {
        DB::table('product_variants')->where('status', 'pre_order')->update(['status' => 'pre-order']);
    }
}
