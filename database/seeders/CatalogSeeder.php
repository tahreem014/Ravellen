<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $mugImage   = 'https://images.unsplash.com/photo-1515401857507-c897a8f64e42?w=600&q=80';
        $bowlImage  = 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=600&q=80';
        $spoonImage = 'https://images.unsplash.com/photo-1585314062340-f1a5a7c9328d?w=600&q=80';
        $giftBoxA   = 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80';
        $giftBoxB   = 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=600&q=80';
        $giftBoxC   = 'https://images.unsplash.com/photo-1607344645866-009c320b63e0?w=600&q=80';

        $catalogue = [
            'Ceramic Items' => [
                'Mugs' => [
                    'image' => $mugImage,
                    'products' => [
                        ['BT21 Coffee Mug',    'Cute BT21 character ceramic mug', 1850, '350 ml'],
                        ['Purple Galaxy Mug',  'Purple gradient ceramic mug',      1650, '350 ml'],
                        ['Aesthetic Daisy Mug','Floral aesthetic mug',             1750, '350 ml'],
                        ['Sakura Blossom Mug', 'Cherry blossom ceramic mug',       1950, '450 ml'],
                        ['Minimal White Mug',  'Plain premium mug',                1450, '350 ml'],
                    ],
                ],
                'Bowls' => [
                    'image' => $bowlImage,
                    'products' => [
                        ['BT21 Ramen Bowl',     'Large BT21 ceramic ramen bowl', 2850, '900 ml'],
                        ['Pastel Dessert Bowl', 'Small dessert bowl',            1450, '450 ml'],
                        ['Sakura Rice Bowl',    'Japanese style bowl',           1650, '550 ml'],
                        ['Cloud Soup Bowl',     'Matte ceramic soup bowl',       1850, '700 ml'],
                    ],
                ],
                'Spoons' => [
                    'image' => $spoonImage,
                    'products' => [
                        ['Sakura Spoon',     'Ceramic spoon',           450, 'Standard'],
                        ['BT21 Spoon',       'Character ceramic spoon', 550, 'Standard'],
                        ['Daisy Spoon',      'Floral ceramic spoon',    450, 'Standard'],
                        ['Matte White Spoon','Minimal ceramic spoon',   400, 'Standard'],
                    ],
                ],
            ],
            'BTS Gift Boxes' => [
                'Era Boxes' => [
                    'image' => $giftBoxA,
                    'products' => [
                        ['Dynamite Era Box',   'Inspired by Dynamite concept', 5500, 'Standard'],
                        ['Butter Era Box',     'Butter themed collection',     5800, 'Standard'],
                        ['BE Album Box',       'Inspired by BE era',           5700, 'Standard'],
                        ['Proof Collector Box','Proof anniversary edition',    6200, 'Premium'],
                        ['Love Yourself Box',  'Love Yourself series',        6500, 'Premium'],
                    ],
                ],
                'BT21 World' => [
                    'image' => $giftBoxB,
                    'products' => [
                        ['Cooky Box',  'Jungkook BT21 collection', 4800, 'Standard'],
                        ['Chimmy Box', 'Jimin BT21 collection',    4800, 'Standard'],
                        ['Tata Box',   'V BT21 collection',        4800, 'Standard'],
                        ['RJ Box',     'Jin BT21 collection',      4800, 'Standard'],
                        ['Shooky Box', 'SUGA BT21 collection',     4800, 'Standard'],
                    ],
                ],
                'OT7 Special' => [
                    'image' => $giftBoxC,
                    'products' => [
                        ['Purple Love Box',       'OT7 themed mystery box',     6500, 'Standard'],
                        ['Festa Celebration Box', 'BTS Festa edition',          7000, 'Premium'],
                        ['ARMY Forever Box',      'Complete OT7 collection',    7500, 'Deluxe'],
                    ],
                ],
            ],
            'Gift Boxes' => [
                'Kuromi' => [
                    'image' => $giftBoxA,
                    'products' => [
                        ['Kuromi Dream Box',  'Kuromi accessories',           4800, 'Standard'],
                        ['Kuromi Deluxe Box', 'Premium Kuromi collection',    6200, 'Premium'],
                    ],
                ],
                'Hello Kitty' => [
                    'image' => $giftBoxB,
                    'products' => [
                        ['Hello Kitty Box',  'Cute pink themed box', 4500, 'Standard'],
                        ['Pink Paradise Box','Premium Kitty edition',6000, 'Premium'],
                    ],
                ],
                'Cinnamoroll' => [
                    'image' => $giftBoxC,
                    'products' => [
                        ['Cinnamoroll Box', 'Cute blue accessories', 4900, 'Standard'],
                        ['Sky Dream Box',   'Premium edition',       6300, 'Premium'],
                    ],
                ],
                'My Melody' => [
                    'image' => $giftBoxA,
                    'products' => [
                        ['My Melody Box',    'Pink accessories', 4800, 'Standard'],
                        ['Sweet Melody Box', 'Premium edition',  6200, 'Premium'],
                    ],
                ],
                'Tom & Jerry' => [
                    'image' => $giftBoxB,
                    'products' => [
                        ['Cheese Chase Box',   'Tom & Jerry themed', 4500, 'Standard'],
                        ['Classic Cartoon Box','Retro collection',   5800, 'Premium'],
                    ],
                ],
                'Ben 10' => [
                    'image' => $giftBoxC,
                    'products' => [
                        ['Omnitrix Box',   'Ben 10 collectibles', 4800, 'Standard'],
                        ['Alien Force Box','Premium edition',     6500, 'Premium'],
                    ],
                ],
                'Pokémon' => [
                    'image' => $giftBoxA,
                    'products' => [
                        ['Pikachu Box','Pikachu collection', 4900, 'Standard'],
                        ['Eevee Box',  'Eevee edition',       5500, 'Premium'],
                    ],
                ],
                'Harry Potter' => [
                    'image' => $giftBoxB,
                    'products' => [
                        ['Hogwarts Box',     'Hogwarts themed',           6000, 'Standard'],
                        ['Wizard Deluxe Box','Premium collector edition', 7500, 'Premium'],
                    ],
                ],
            ],
        ];

        foreach ($catalogue as $categoryName => $subCategories) {
            $category = Category::firstOrCreate(['name' => $categoryName]);

            foreach ($subCategories as $subCategoryName => $data) {
                $subCategory = SubCategory::firstOrCreate([
                    'category_id' => $category->id,
                    'name'        => $subCategoryName,
                ]);

                foreach ($data['products'] as [$productName, $description, $price, $variantName]) {
                    $product = Product::updateOrCreate(
                        [
                            'category_id'     => $category->id,
                            'sub_category_id' => $subCategory->id,
                            'name'            => $productName,
                        ],
                        ['description' => $description]
                    );

                    $product->variants()->updateOrCreate(
                        ['variant_name' => $variantName],
                        [
                            'price'   => $price,
                            'min_qty' => 1,
                            'max_qty' => 10,
                            'stock'   => 50,
                            'status'  => $categoryName === 'Ceramic Items' ? \App\Models\ProductVariant::STATUS_PRE_ORDER : \App\Models\ProductVariant::STATUS_ACTIVE,
                        ]
                    );

                    $product->images()->updateOrCreate(
                        ['image' => $data['image']],
                        ['video' => null]
                    );
                }
            }
        }
    }
}
