<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The top bar keeps wishlist, cart and the menu button on every page, signed in or not
 * (so they sit beside the menu button on a phone); the bell and avatar are for accounts. Rolled back.
 */
class NavbarIconsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guests_get_wishlist_cart_bell_and_the_menu_button(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('id="wishlist-badge"', false)
            ->assertSee('id="cart-badge"', false)
            ->assertSee('id="notif-bell-btn"', false)
            ->assertSee('data-bs-target="#mobileNav"', false)
            ->assertSee(route('wish-list'), false)
            ->assertSee(url('cart'), false)
            // a guest's bell invites them to sign in and has no avatar next to it
            ->assertSee('Sign in to see your order updates and notifications.')
            ->assertDontSee('class="rv-avatar-btn"', false)
            ->assertDontSee('id="notif-dropdown-list"', false)
            // the slide-out menu offers Login / Register, not a Profile page they can't open
            ->assertSee('rv-register-btn', false)
            ->assertDontSee('ri-user-line', false);
    }

    public function test_every_page_but_home_has_a_back_arrow_that_falls_back_to_a_parent_page(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="rv-back-btn"', false);

        // The arrow's own link is where it goes when the visitor didn't arrive from another page on the site
        $backLinkTo = fn (string $href) => '#<a href="' . preg_quote($href, '#') . '"\s+class="rv-back-btn"#';

        $product = Product::create(['name' => 'Back Test ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);

        foreach (['/shop', '/bts-merch', '/gift-boxes', '/about', '/mystery-balls'] as $url) {
            $this->get($url)->assertOk()->assertSee('id="rv-back-btn"', false)->assertSee('onclick="return rvGoBack(event)"', false);
        }

        // it sits under the top bar, not inside it
        $shop = $this->get('/shop')->getContent();
        $this->assertGreaterThan(strpos($shop, '</nav>'), strpos($shop, 'id="rv-back-btn"'));

        $this->assertMatchesRegularExpression($backLinkTo(url('/')), $shop);
        $this->assertMatchesRegularExpression($backLinkTo(url('/shop')), $this->get(route('product-item', $product))->assertOk()->getContent());
    }

    public function test_shop_bts_and_category_pages_keep_their_selection_in_the_url(): void
    {
        $this->get('/shop')->assertOk()->assertSee('function restoreShopView', false)->assertSee('history.replaceState', false);
        $this->get('/bts-merch')->assertOk()->assertSee("get('bias')", false)->assertSee("'?bias='", false);
        $this->get('/gift-boxes')->assertOk()->assertSee("get('filter')", false);
    }

    public function test_a_guests_wishlist_badge_counts_their_session_wishlist(): void
    {
        $item = ['name' => 'X', 'price' => 'PKR 1', 'image' => 'x.jpg'];

        $this->withSession(['wishlist' => ['p-1' => $item + ['id' => 'p-1'], 'p-2' => $item + ['id' => 'p-2']]])
            ->get('/shop')
            ->assertOk()
            ->assertSee('<span class="cart-badge" id="wishlist-badge" style="">2</span>', false);

        $this->flushSession();
        $this->get('/shop')->assertSee('id="wishlist-badge" style="display:none;"', false);
    }

    public function test_signed_in_customers_also_get_the_bell_and_their_avatar(): void
    {
        $customer = User::factory()->create();
        $product = Product::create(['name' => 'Nav Test ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);
        $customer->wishLists()->create(['product_id' => $product->id]);

        $this->actingAs($customer)->get('/shop')
            ->assertOk()
            ->assertSee('id="wishlist-badge"', false)
            ->assertSee('id="cart-badge"', false)
            ->assertSee('id="notif-bell-btn"', false)
            ->assertSee('class="rv-avatar-btn"', false)
            ->assertSee('<span class="cart-badge" id="wishlist-badge" style="">1</span>', false)
            ->assertSee('data-bs-target="#mobileNav"', false);
    }
}
