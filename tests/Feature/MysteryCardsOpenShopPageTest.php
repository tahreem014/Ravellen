<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Mystery Scoops and Mystery Balls have one page each: the shop's product page. The home cards open it, and
 * the bare /mystery-scoops and /mystery-balls URLs only send visitors there (?bias= still serves the BTS Merch
 * member pages). Rolled back.
 */
class MysteryCardsOpenShopPageTest extends TestCase
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
