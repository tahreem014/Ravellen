<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Every page's browser tab reads "Page | Ravellen" and shows the Ravellen
 * logo icon (layouts/title-meta). Rolled back.
 */
class PageTitlesTest extends TestCase
{
    use DatabaseTransactions;

    private function assertTab(TestResponse $response, string $expected): void
    {
        preg_match('#<title>(.*?)</title>#s', $response->getContent(), $match);
        $this->assertSame($expected, trim(preg_replace('/\s+/', ' ', html_entity_decode($match[1] ?? ''))));
        $response->assertSee('<link rel="icon" href="' . asset('favicon.ico'), false);
    }

    public function test_storefront_and_auth_pages_name_themselves_then_ravellen(): void
    {
        $product = Product::create(['name' => 'Title Test Box ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);

        foreach ([
            '/'                     => 'Home',
            '/shop'                 => 'Shop',
            '/about'                => 'About Us',
            '/contact'              => 'Contact Us',
            '/mystery-balls'        => 'Mystery Balls',
            '/mystery-scoops'       => 'Mystery Scoops',
            '/bts-merch'            => 'BTS Merch',
            '/gift-boxes'           => 'Mystery Gift Boxes',
            '/bts-gift-boxes'       => 'BTS Gift Boxes',
            '/ceramic-items'        => 'Ceramic Collection',
            '/gift-box?item=cooky-box' => 'Cooky Box',
            '/cart'                 => 'Shopping Cart',
            '/wish-list'            => 'My Wishlist',
            '/login'                => 'Sign In',
            '/register'             => 'Sign Up',
            '/password/reset'       => 'Forgot Password',
            '/catalog-browser'      => 'Catalog Data Browser',
            route('product-item', $product) => $product->name,
        ] as $url => $name) {
            $this->assertTab($this->get($url)->assertOk(), "$name | Ravellen");
        }

        $this->assertTab($this->get('/no-such-page-' . uniqid())->assertNotFound(), 'Not Found | Ravellen');
    }

    public function test_signed_in_and_admin_pages_name_themselves_then_ravellen(): void
    {
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id, 'order_type' => 'normal', 'shipping_name' => 'Title Test', 'shipping_address' => 'Test street',
            'total_amount' => 1000, 'order_date' => now(), 'order_status' => 'confirmed', 'payment_method' => 'credit_card',
            'payment_status' => 'paid', 'tracking_number' => 'RVL-TITLES',
        ]);

        foreach (['/checkout' => 'Checkout', '/profile' => 'My Profile', '/notifications' => 'Notifications', '/track-order' => 'Track Order',
                  route('orders.show', $order) => 'RVL-TITLES'] as $url => $name) {   // customers see the tracking number, never the id
            $this->assertTab($this->actingAs($customer)->get($url)->assertOk(), "$name | Ravellen");
        }

        $admin = User::factory()->create(['is_admin' => true]);
        $this->assertTab($this->actingAs($admin)->get(route('admin.products.index'))->assertOk(), 'Products | Ravellen');
        $this->assertTab($this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk(), "Order #{$order->id} | Ravellen");
    }

    public function test_icon_files_are_the_ravellen_logo(): void
    {
        $this->assertSame("\0\0\1\0\3\0", substr(file_get_contents(public_path('favicon.ico')), 0, 6));   // ICO with 16/32/48
        $this->assertSame([180, 180], array_slice(getimagesize(public_path('apple-touch-icon.png')), 0, 2));
    }
}
