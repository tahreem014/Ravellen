<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The home cards open the shop, not pages of their own. Mystery Scoops and Mystery Balls open the shop's product
 * page (and the bare /mystery-scoops and /mystery-balls URLs only send visitors there; ?bias= still serves the
 * BTS Merch member pages). BTS Merch and Gift Boxes open the shop on that category. Rolled back.
 */
class HomeCardsOpenShopPageTest extends TestCase
{
    use DatabaseTransactions;

    /** The product the old pages used to show (same lookup as HomeController::genericProductPage), made if the database has none. */
    private function canonicalProduct(string $category, string $name): Product
    {
        return Product::whereHas('category', fn ($q) => $q->where('name', $category))
            ->orderByRaw('name = ? desc', [$name])->orderBy('id')->first()
            ?? Product::create(['name' => $name, 'category_id' => Category::firstOrCreate(['name' => $category])->id]);
    }

    public function test_home_cards_open_the_shops_product_pages(): void
    {
        $this->canonicalProduct('Mystery Scoops', 'Original Mystery Scoop');
        $this->canonicalProduct('Mystery Balls', 'Original Mystery Balls');

        $home = $this->get('/')->assertOk();

        foreach (['Mystery Scoops', 'Mystery Balls'] as $category) {
            $product = Category::where('name', $category)->first()->firstProduct();

            // the hero card and the "Shop by Category" card
            $this->assertSame(2, substr_count($home->getContent(), 'href="' . route('product-item', $product->slug) . '"'), $category);
        }

        $home->assertDontSee('href="' . url('mystery-scoops') . '"', false)
            ->assertDontSee('href="' . url('mystery-balls') . '"', false);
    }

    public function test_gift_box_cards_open_the_shop_on_that_category(): void
    {
        $category = Category::firstOrCreate(['name' => 'Gift Boxes']);
        $home = $this->get('/')->assertOk();

        // the hero card and the "Shop by Category" card
        $this->assertSame(2, substr_count($home->getContent(), 'href="' . route('shop', ['category' => $category->id]) . '"'));
        $home->assertDontSee('href="' . url('gift-boxes') . '"', false);

        $this->get(route('shop', ['category' => $category->id]))->assertOk()->assertSee('data-cat="' . $category->id . '"', false);
    }

    public function test_bts_merch_cards_keep_their_own_member_page(): void
    {
        // the hero card, the "BTS Collection" button and the "Shop by Category" card
        $this->assertSame(3, substr_count($this->get('/')->assertOk()->getContent(), 'href="' . url('bts-merch') . '"'));
    }

    public function test_the_old_urls_send_visitors_to_the_shops_page(): void
    {
        $scoop = $this->canonicalProduct('Mystery Scoops', 'Original Mystery Scoop');
        $balls = $this->canonicalProduct('Mystery Balls', 'Original Mystery Balls');

        $this->get(route('mystery-scoops'))->assertRedirect(route('product-item', $scoop->slug));
        $this->get(route('mystery-balls'))->assertRedirect(route('product-item', $balls->slug));

        $this->get(route('product-item', $scoop->slug))->assertOk()->assertSee($scoop->name);
        $this->get(route('product-item', $balls->slug))->assertOk()->assertSee($balls->name);
    }

    public function test_bts_member_pages_keep_their_own_url(): void
    {
        $this->canonicalProduct('Mystery Scoops', 'Original Mystery Scoop');
        $this->canonicalProduct('Mystery Balls', 'Original Mystery Balls');

        // not redirected: the member's own page (or "Coming soon" while BTS Merch has no products)
        $this->get(route('mystery-scoops', ['bias' => 'suga']))->assertOk();
        $this->get(route('mystery-balls', ['bias' => 'jungkook']))->assertOk();
    }
}
