<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SplitBtsGiftBoxesCategory extends Migration
{
    private array $btsSubNames = ['Era Boxes', 'BT21 World', 'OT7 Special'];

    public function up()
    {
        $giftBoxesId = DB::table('categories')->where('name', 'Gift Boxes')->value('id');

        if (!$giftBoxesId) {
            return;
        }

        $btsGiftBoxesId = DB::table('categories')->where('name', 'BTS Gift Boxes')->value('id');

        if (!$btsGiftBoxesId) {
            $btsGiftBoxesId = DB::table('categories')->insertGetId([
                'name'       => 'BTS Gift Boxes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $subIds = DB::table('sub_categories')
            ->where('category_id', $giftBoxesId)
            ->whereIn('name', $this->btsSubNames)
            ->pluck('id');

        DB::table('sub_categories')->whereIn('id', $subIds)->update(['category_id' => $btsGiftBoxesId]);
        DB::table('products')->whereIn('sub_category_id', $subIds)->update(['category_id' => $btsGiftBoxesId]);
    }

    public function down()
    {
        $giftBoxesId    = DB::table('categories')->where('name', 'Gift Boxes')->value('id');
        $btsGiftBoxesId = DB::table('categories')->where('name', 'BTS Gift Boxes')->value('id');

        if (!$giftBoxesId || !$btsGiftBoxesId) {
            return;
        }

        $subIds = DB::table('sub_categories')
            ->where('category_id', $btsGiftBoxesId)
            ->whereIn('name', $this->btsSubNames)
            ->pluck('id');

        DB::table('sub_categories')->whereIn('id', $subIds)->update(['category_id' => $giftBoxesId]);
        DB::table('products')->whereIn('sub_category_id', $subIds)->update(['category_id' => $giftBoxesId]);
        DB::table('categories')->where('id', $btsGiftBoxesId)->delete();
    }
}
