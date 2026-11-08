<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * In the shop, a BTS member's Scoop / Balls open the member pages the BTS Merch page's picker leads to
 * (/mystery-scoops?bias=… and /mystery-balls?bias=…); every other product opens its own product page. Rolled back.
 */
class ShopBtsMerchLinksTest extends TestCase
{
    use DatabaseTransactions;

    private function product(Category $category, string $name, ?SubCategory $sub = null): Product
    {
        $product = Product::create(['name' => $name . ' ' . uniqid(), 'category_id' => $category->id, 'sub_category_id' => $sub?->id]);
        $product->variants()->create(['variant_name' => 'Item', 'price' => 1500, 'status' => 'active']);

        return $product;
    }

    /** The "View Product" link of a product's card in the shop. */
    private function cardLink(string $html, Product $product): ?string
    {
        preg_match('/data-live-product="' . $product->id . '".*?<a href="([^"]+)" class="shop-explore-btn"/s', $html, $match);

        return $match[1] ?? null;
    }

    public function test_bts_member_products_open_the_member_pages(): void
    {
        $bts = Category::firstOrCreate(['name' => 'BTS Merch']);
        Product::where('category_id', $bts->id)->update(['category_id' => Category::create(['name' => 'Scratch ' . uniqid()])->id, 'sub_category_id' => null]);

        $jungkook = SubCategory::firstOrCreate(['category_id' => $bts->id, 'name' => 'Jungkook']);
        $suga     = SubCategory::firstOrCreate(['category_id' => $bts->id, 'name' => 'SUGA']);
        $other    = SubCategory::firstOrCreate(['category_id' => $bts->id, 'name' => 'Not A Member']);

        $scoop      = $this->product($bts, 'Jungkook Mystery Scoop', $jungkook);
        $balls      = $this->product($bts, 'Jungkook Mystery Balls', $jungkook);
        $extraScoop = $this->product($bts, 'Jungkook Extra Scoop', $jungkook);   // the member page shows the first scoop only
        $sugaScoop  = $this->product($bts, 'SUGA Mystery Scoop', $suga);
        $stranger   = $this->product($bts, 'Mystery Scoop', $other);             // a sub-category that is no member
        $loose      = $this->product($bts, 'Mystery Scoop');                     // no sub-category at all

        $html = $this->get(route('shop'))->assertOk()->getContent();

        $this->assertSame(route('mystery-scoops', ['bias' => 'jungkook']), html_entity_decode($this->cardLink($html, $scoop)));
        $this->assertSame(route('mystery-balls', ['bias' => 'jungkook']), html_entity_decode($this->cardLink($html, $balls)));
        $this->assertSame(route('mystery-scoops', ['bias' => 'suga']), html_entity_decode($this->cardLink($html, $sugaScoop)));

        foreach ([$extraScoop, $stranger, $loose] as $product) {
            $this->assertSame(route('product-item', $product->slug), $this->cardLink($html, $product), $product->name);
        }
    }

    public function test_other_categories_keep_their_product_pages(): void
    {
        $boxes = Category::firstOrCreate(['name' => 'Gift Boxes']);
        $box   = $this->product($boxes, 'Jungkook Mystery Scoop Box');   // named like a BTS scoop, but not BTS Merch

        $this->assertSame(route('product-item', $box->slug), $this->cardLink($this->get(route('shop'))->assertOk()->getContent(), $box));
    }
}
