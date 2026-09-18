<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class MysteryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $scoopImage = 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=600&q=80';
        $ballsImage = 'https://images.unsplash.com/photo-1615485925694-a031e97d8964?w=600&q=80';
        $btsImage   = 'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=600&q=80';

        // ── Mystery Scoops (no subcategory) ──────────────────────────────────
        $mysteryScoops = Category::firstOrCreate(['name' => 'Mystery Scoops']);

        $this->createProduct(
            $mysteryScoops,
            null,
            'Original Mystery Scoop',
            'Random surprise collection with cute accessories and collectibles.',
            ['Classic Scoop' => 2500, 'Premium Scoop' => 4500, 'Deluxe Scoop' => 6500],
            $scoopImage
        );

        // ── Mystery Balls (no subcategory) ───────────────────────────────────
        $mysteryBalls = Category::firstOrCreate(['name' => 'Mystery Balls']);

        $this->createProduct(
            $mysteryBalls,
            null,
            'Original Mystery Balls',
            'Random surprise mystery balls filled with exciting mini collectibles.',
            $this->ballsLadder([5 => 1000, 10 => 1900, 20 => 3600, 50 => 8500]),
            $ballsImage
        );

        // ── BTS Merch ─────────────────────────────────────────────────────────
        $btsMerch = Category::firstOrCreate(['name' => 'BTS Merch']);

        $memberScoopPrices = ['Classic Scoop' => 2900, 'Premium Scoop' => 4900, 'Deluxe Scoop' => 6900];
        $memberBallsAnchors = [5 => 1200, 10 => 2200, 20 => 4200];

        // Members that share identical Scoop/Balls pricing (only Jungkook's numbers
        // were given explicitly; Jimin's repeats them and the rest follow the same
        // established per-member pattern).
        $members = [
            'Jungkook'     => 'Jungkook',
            'Jimin'        => 'Jimin',
            'V (Taehyung)' => 'Taehyung',
            'RM (Namjoon)' => 'RM',
            'SUGA'         => 'SUGA',
            'J-Hope'       => 'J-Hope',
            'Jin'          => 'Jin',
        ];

        foreach ($members as $subCategoryName => $productPrefix) {
            $sub = SubCategory::firstOrCreate([
                'category_id' => $btsMerch->id,
                'name'        => $subCategoryName,
            ]);

            $this->createProduct(
                $btsMerch,
                $sub,
                "{$productPrefix} Mystery Scoop",
                "Surprise scoop featuring {$productPrefix}-themed merchandise.",
                $memberScoopPrices,
                $btsImage
            );

            $this->createProduct(
                $btsMerch,
                $sub,
                "{$productPrefix} Mystery Balls",
                "Mystery balls inspired by {$productPrefix}.",
                $this->ballsLadder($memberBallsAnchors),
                $btsImage
            );
        }

        // OT7 — its own distinct pricing
        $ot7 = SubCategory::firstOrCreate(['category_id' => $btsMerch->id, 'name' => 'OT7']);

        $this->createProduct(
            $btsMerch,
            $ot7,
            'OT7 Mystery Scoop',
            'Mystery scoop featuring all seven BTS members.',
            ['Classic Scoop' => 3200, 'Premium Scoop' => 5200, 'Deluxe Scoop' => 7500],
            $btsImage
        );

        $this->createProduct(
            $btsMerch,
            $ot7,
            'OT7 Mystery Balls',
            'Mystery balls inspired by the complete BTS group.',
            $this->ballsLadder([5 => 1400, 10 => 2600, 20 => 4800]),
            $btsImage
        );

        // BT21 — its own distinct pricing
        $bt21 = SubCategory::firstOrCreate(['category_id' => $btsMerch->id, 'name' => 'BT21']);

        $this->createProduct(
            $btsMerch,
            $bt21,
            'BT21 Mystery Scoop',
            'Surprise scoop with BT21 collectibles and accessories.',
            ['Classic Scoop' => 3000, 'Premium Scoop' => 5000, 'Deluxe Scoop' => 7000],
            $btsImage
        );

        $this->createProduct(
            $btsMerch,
            $bt21,
            'BT21 Mystery Balls',
            'Mystery balls containing BT21-themed goodies.',
            $this->ballsLadder([5 => 1300, 10 => 2500, 20 => 4500]),
            $btsImage
        );
    }

    /**
     * Build the full "Pack of 5, 10, 15 ... 50" ladder from a sparse set of
     * known (qty => price) anchors: known quantities keep their given price,
     * gaps are linearly interpolated between the surrounding anchors, and
     * quantities beyond the last anchor are extrapolated using that last
     * segment's per-unit slope. Prices are rounded to the nearest 50.
     *
     * @param  array<int, int>  $anchors
     * @return array<string, int>
     */
    private function ballsLadder(array $anchors): array
    {
        ksort($anchors);
        $anchorQtys = array_keys($anchors);

        $prices = [];

        foreach (range(5, 50, 5) as $qty) {
            if (isset($anchors[$qty])) {
                $prices[$qty] = $anchors[$qty];
                continue;
            }

            $lower = null;
            $upper = null;

            foreach ($anchorQtys as $aQty) {
                if ($aQty < $qty) {
                    $lower = $aQty;
                }
                if ($aQty > $qty && $upper === null) {
                    $upper = $aQty;
                }
            }

            if ($upper !== null) {
                $slope = ($anchors[$upper] - $anchors[$lower]) / ($upper - $lower);
                $prices[$qty] = (int) (round(($anchors[$lower] + $slope * ($qty - $lower)) / 50) * 50);
            } else {
                $lastQty = max($anchorQtys);
                $prevQty = null;
                foreach ($anchorQtys as $aQty) {
                    if ($aQty < $lastQty) {
                        $prevQty = $aQty;
                    }
                }
                $slope = ($anchors[$lastQty] - $anchors[$prevQty]) / ($lastQty - $prevQty);
                $prices[$qty] = (int) (round(($anchors[$lastQty] + $slope * ($qty - $lastQty)) / 50) * 50);
            }
        }

        $variants = [];
        foreach ($prices as $qty => $price) {
            $variants["Pack of {$qty}"] = $price;
        }

        return $variants;
    }

    /**
     * @param  array<string, int>  $variants
     */
    private function createProduct(
        Category $category,
        ?SubCategory $subCategory,
        string $name,
        string $description,
        array $variants,
        string $image
    ): void {
        $product = Product::updateOrCreate(
            [
                'category_id'     => $category->id,
                'sub_category_id' => $subCategory?->id,
                'name'            => $name,
            ],
            ['description' => $description]
        );

        foreach ($variants as $variantName => $price) {
            $product->variants()->updateOrCreate(
                ['variant_name' => $variantName],
                [
                    'price'   => $price,
                    'min_qty' => 1,
                    'max_qty' => 10,
                    'stock'   => 50,
                    'status'  => 'active',
                ]
            );
        }

        $product->images()->updateOrCreate(['image' => $image], ['video' => null]);
    }
}
