<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * A category with no products shows "Coming soon" instead of an empty page; as soon as it
 * has a product (in stock or not) the products show, and it flips back when the last one
 * goes. Nothing is stored for it: it's derived from the products. Rolled back.
 */
class ComingSoonCategoryTest extends TestCase
{
    use DatabaseTransactions;

    /** Empties a category by moving its products to a scratch one (nothing is deleted). */
    private function emptyCategory(string $name): Category
    {
        $category = Category::firstOrCreate(['name' => $name]);
        $scratch  = Category::create(['name' => 'Scratch ' . uniqid()]);

        Product::where('category_id', $category->id)->update(['category_id' => $scratch->id, 'sub_category_id' => null]);

        return $category;
    }

    private function productIn(Category $category, string $status = ProductVariant::STATUS_ACTIVE): Product
    {
        $product = Product::create([
            'name'        => 'Test Product ' . uniqid(),
            'category_id' => $category->id,
        ]);
        $product->variants()->create(['variant_name' => 'Item', 'price' => 1500, 'status' => $status]);

        return $product;
    }

    private function homeHtml(): string
    {
        return preg_replace('/\s+/', ' ', $this->get(route('root'))->assertOk()->getContent());
    }

    private function shopPillIsFlagged(string $name): bool
    {
        $html = $this->get(route('shop'))->assertOk()->getContent();

        return (bool) preg_match('/data-name="' . preg_quote($name, '/') . '"\s+data-coming-soon="1"/', $html);
    }

    public function test_an_empty_category_says_coming_soon_everywhere(): void
    {
        $ceramic = $this->emptyCategory('Ceramic Items');

        $this->get(route('ceramic-items'))
            ->assertOk()
            ->assertSee('Our ceramic pieces are on the way')
            ->assertSee('data-live-listing="category:Ceramic Items"', false)
            ->assertDontSee('id="cer-grid"', false)
            ->assertDontSee('<button class="cer-filter-btn', false);

        $this->assertTrue($this->shopPillIsFlagged('Ceramic Items'));

        $home = $this->homeHtml();
        $this->assertStringContainsString('Coming Soon', $home);
        $this->assertStringNotContainsString('Explore Ceramic Items', $home);
    }

    public function test_gift_box_pages_say_coming_soon_when_empty(): void
    {
        $this->emptyCategory('Gift Boxes');
        $this->emptyCategory('BTS Gift Boxes');

        $this->get(route('gift-boxes'))->assertOk()
            ->assertSee('Our gift boxes are on the way')
            ->assertDontSee('id="gb-grid"', false);

        $this->get(route('bts-gift-boxes'))->assertOk()
            ->assertSee('Our BTS gift boxes are on the way')
            ->assertDontSee('id="bts-gb-grid"', false);
    }

    public function test_the_bts_merch_page_says_coming_soon_until_it_has_products(): void
    {
        $bts = $this->emptyCategory('BTS Merch');

        $this->get(route('bts-merch'))->assertOk()
            ->assertSee('Our BTS merch is on the way')
            ->assertSee('data-live-listing="category:BTS Merch"', false)
            ->assertDontSee('Choose Your Bias')
            ->assertDontSee('id="dynamicCards"', false);
        $this->assertTrue($this->shopPillIsFlagged('BTS Merch'));

        $this->productIn($bts);

        $this->get(route('bts-merch'))->assertOk()
            ->assertSee('Choose Your Bias')
            ->assertSee('id="dynamicCards"', false)
            ->assertDontSee('Our BTS merch is on the way');
        $this->assertFalse($this->shopPillIsFlagged('BTS Merch'));
    }

    public function test_a_category_with_products_lists_them(): void
    {
        $ceramic = $this->emptyCategory('Ceramic Items');
        $mug     = $this->productIn($ceramic);

        $this->get(route('ceramic-items'))
            ->assertOk()
            ->assertSee($mug->name)
            ->assertSee('id="cer-grid"', false)
            ->assertDontSee('Our ceramic pieces are on the way');

        $this->assertFalse($this->shopPillIsFlagged('Ceramic Items'));
        $this->get(route('shop'))->assertSee($mug->name);

        $this->assertStringContainsString('Explore Ceramic Items', $this->homeHtml());
        $this->get(route('product-item', $mug))->assertOk();
    }

    public function test_out_of_stock_products_are_not_coming_soon(): void
    {
        $ceramic = $this->emptyCategory('Ceramic Items');
        $sold    = $this->productIn($ceramic, ProductVariant::STATUS_OUT_OF_STOCK);
        $later   = $this->productIn($ceramic, ProductVariant::STATUS_RESTOCKING);

        $this->get(route('ceramic-items'))
            ->assertOk()
            ->assertSee($sold->name)
            ->assertSee($later->name)
            ->assertDontSee('Our ceramic pieces are on the way');

        $this->assertFalse($this->shopPillIsFlagged('Ceramic Items'));
        $this->assertStringContainsString('Explore Ceramic Items', $this->homeHtml());
    }

    public function test_coming_soon_goes_when_a_product_is_added_and_returns_when_the_last_is_removed(): void
    {
        $ceramic = $this->emptyCategory('Ceramic Items');
        $live    = app(LiveUpdateService::class);

        $emptyShop     = $live->listingSignature('shop');
        $emptyCategory = $live->listingSignature('category:Ceramic Items');
        $this->get(route('ceramic-items'))->assertSee('Our ceramic pieces are on the way');

        $mug = $this->productIn($ceramic);

        $this->get(route('ceramic-items'))->assertSee($mug->name)->assertDontSee('Our ceramic pieces are on the way');
        // open pages watch these fingerprints and reload when they change
        $this->assertNotSame($emptyShop, $live->listingSignature('shop'));
        $this->assertNotSame($emptyCategory, $live->listingSignature('category:Ceramic Items'));

        $mug->delete();

        $this->get(route('ceramic-items'))->assertSee('Our ceramic pieces are on the way')->assertDontSee($mug->name);
        $this->assertSame($emptyShop, $live->listingSignature('shop'));
    }

    public function test_direct_product_pages_of_an_empty_category_show_only_coming_soon(): void
    {
        $ceramic = $this->emptyCategory('Ceramic Items');
        foreach (['Gift Boxes', 'Mystery Balls', 'Mystery Scoops', 'BTS Merch'] as $name) {
            $this->emptyCategory($name);
        }

        $pages = [
            route('ceramic-mug')                                  => 'Ceramic Items',
            '/product/ceramic'                                    => 'Ceramic Items',
            route('gift-box')                                     => 'Gift Boxes',
            route('mystery-balls')                                => 'Mystery Balls',
            route('mystery-scoops')                               => 'Mystery Scoops',
            route('mystery-balls', ['bias' => 'jungkook'])        => 'BTS Merch',
            route('mystery-scoops', ['bias' => 'jungkook'])       => 'BTS Merch',
            '/product/bts-scoop'                                  => 'BTS Merch',
        ];

        foreach ($pages as $url => $name) {
            $this->get($url)->assertOk()
                ->assertSee("We're getting {$name} ready")
                ->assertSee('data-live-listing="category:' . $name . '"', false)
                ->assertDontSee('pd-wrapper', false);
        }

        // once the category has a product its own page is back
        $this->productIn($ceramic);
        $this->get(route('ceramic-mug'))->assertOk()->assertDontSee("We're getting Ceramic Items ready");
    }

    public function test_other_categories_are_unaffected(): void
    {
        $this->emptyCategory('Ceramic Items');
        $box = $this->productIn(Category::firstOrCreate(['name' => 'Gift Boxes']));

        $this->get(route('shop'))->assertOk()->assertSee($box->name);
        $this->get(route('gift-boxes'))->assertOk()->assertSee($box->name)->assertDontSee('Our gift boxes are on the way');
        $this->get(route('product-item', $box))->assertOk();
    }
}
