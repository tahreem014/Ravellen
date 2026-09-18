<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MergeGiftBoxCategories extends Migration
{
    /**
     * Sub-category names that belonged to each pre-merge category, used to
     * split them back apart in down() by name rather than by remembered id.
     */
    private array $mergedNames = [
        'BTS Gift Boxes'    => ['Era Boxes', 'BT21 World', 'OT7 Special'],
        'Sanrio Gift Boxes' => ['Kuromi', 'Hello Kitty', 'Cinnamoroll', 'My Melody'],
        'Cartoon Special'   => ['Tom & Jerry', 'Ben 10', 'Pokémon', 'Harry Potter'],
    ];

    public function up()
    {
        $giftBoxes = DB::table('categories')->where('name', 'BTS Gift Boxes')->first();

        if ($giftBoxes) {
            DB::table('categories')->where('id', $giftBoxes->id)->update(['name' => 'Gift Boxes']);
            $giftBoxesId = $giftBoxes->id;
        } else {
            $giftBoxesId = DB::table('categories')->where('name', 'Gift Boxes')->value('id');
            if (!$giftBoxesId) {
                $giftBoxesId = DB::table('categories')->insertGetId([
                    'name'       => 'Gift Boxes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (['Sanrio Gift Boxes', 'Cartoon Special'] as $oldName) {
            $old = DB::table('categories')->where('name', $oldName)->first();

            if (!$old) {
                continue;
            }

            DB::table('sub_categories')->where('category_id', $old->id)->update(['category_id' => $giftBoxesId]);
            DB::table('products')->where('category_id', $old->id)->update(['category_id' => $giftBoxesId]);
            DB::table('categories')->where('id', $old->id)->delete();
        }
    }

    public function down()
    {
        $giftBoxesId = DB::table('categories')->where('name', 'Gift Boxes')->value('id');

        if (!$giftBoxesId) {
            return;
        }

        DB::table('categories')->where('id', $giftBoxesId)->update(['name' => 'BTS Gift Boxes']);

        foreach (['Sanrio Gift Boxes', 'Cartoon Special'] as $oldName) {
            $subNames = $this->mergedNames[$oldName];

            $oldId = DB::table('categories')->insertGetId([
                'name'       => $oldName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sub_categories')
                ->where('category_id', $giftBoxesId)
                ->whereIn('name', $subNames)
                ->update(['category_id' => $oldId]);

            DB::table('products')
                ->whereIn('sub_category_id', function ($query) use ($oldId) {
                    $query->select('id')->from('sub_categories')->where('category_id', $oldId);
                })
                ->update(['category_id' => $oldId]);
        }
    }
}
