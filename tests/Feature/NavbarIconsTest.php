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

    public function test_guests_get_wishlist_cart_and_the_menu_button_but_no_bell(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertSee('id="wishlist-badge"', false)
            ->assertSee('id="cart-badge"', false)
            ->assertSee('data-bs-target="#mobileNav"', false)
            ->assertSee(route('wish-list'), false)
            ->assertSee(url('cart'), false)
            ->assertDontSee('id="notif-bell-btn"', false);
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
