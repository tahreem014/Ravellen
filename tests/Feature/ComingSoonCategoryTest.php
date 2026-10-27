<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Ceramic Items has nothing on sale yet (Category::COMING_SOON): the storefront
 * shows "Coming soon" instead of its products, while they stay in the database.
 * Rolled back.
 */
class ComingSoonCategoryTest extends TestCase
{
    use DatabaseTransactions;

    private function ceramicProduct(): Product
    {
        $product = Product::create([
            'name'        => 'Hidden Test Mug ' . uniqid(),
            'category_id' => Category::firstOrCreate(['name' => 'Ceramic Items'])->id,
        ]);
        $product->variants()->create(['variant_name' => 'Mug', 'price' => 1500]);

        return $product;
    }

    public function test_ceramic_page_says_coming_soon_and_lists_no_products(): void
    {
        $mug = $this->ceramicProduct();

        $this->get(route('ceramic-items'))
            ->assertOk()
            ->assertSee('Coming Soon')
            ->assertDontSee($mug->name)
            ->assertDontSee('id="cer-grid"', false)
            ->assertDontSee('<button class="cer-filter-btn', false);
    }

    public function test_shop_hides_ceramic_products_and_flags_the_pill(): void
    {
        $mug = $this->ceramicProduct();

        $this->get(route('shop'))
            ->assertOk()
            ->assertDontSee($mug->name)
            ->assertSee('data-coming-soon="1"', false)
            ->assertSee('id="shop-coming-soon"', false);
    }

    public function test_home_card_says_coming_soon_without_a_price(): void
    {
        $this->ceramicProduct();

        $html = $this->get(route('root'))->assertOk()->getContent();

        $this->assertStringContainsString('Coming Soon', $html);
        $this->assertStringNotContainsString('Explore Ceramic Items', preg_replace('/\s+/', ' ', $html));
    }

    public function test_ceramic_product_and_mug_pages_go_to_the_coming_soon_page(): void
    {
        $mug = $this->ceramicProduct();

        $this->get(route('product-item', $mug))->assertRedirect(route('ceramic-items'));
        $this->get(route('ceramic-mug'))->assertRedirect(route('ceramic-items'));
        $this->get('/product/ceramic')->assertRedirect(route('ceramic-items'));
    }

    public function test_other_categories_are_unaffected(): void
    {
        $box = Product::create([
            'name'        => 'Visible Test Box ' . uniqid(),
            'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id,
        ]);
        $box->variants()->create(['variant_name' => 'Box', 'price' => 2500]);

        $this->get(route('shop'))->assertOk()->assertSee($box->name);
        $this->get(route('product-item', $box))->assertOk();
    }

    public function test_the_shop_live_fingerprint_ignores_hidden_products(): void
    {
        $before = app(\App\Services\LiveUpdateService::class)->listingSignature('shop');
        $this->ceramicProduct();

        $this->assertSame($before, app(\App\Services\LiveUpdateService::class)->listingSignature('shop'));
    }
}
