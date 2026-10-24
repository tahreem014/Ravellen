<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductOnSaleNotification;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Storefront pages updating themselves from GET /live, and customers being
 * notified when a product goes on sale. Rolled back after each test.
 */
class LiveUpdatesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $customer;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin    = User::factory()->create(['is_admin' => true]);
        $this->customer = User::factory()->create();
        $this->category = Category::firstOrCreate(['name' => 'Gift Boxes']);
    }

    public function test_live_endpoint_answers_briefly_until_the_catalog_changes(): void
    {
        $product = $this->product(['Standard' => 4000]);
        $version = app(LiveUpdateService::class)->version();

        $this->getJson(route('live', ['v' => $version, 'products' => [$product->id]]))
            ->assertOk()
            ->assertExactJson(['version' => $version, 'changed' => false]);

        $product->variants->first()->update(['sale_price' => 3000]);
        $newVersion = app(LiveUpdateService::class)->version();
        $this->assertNotSame($version, $newVersion, 'a price change bumps the version');

        $this->getJson(route('live', ['v' => $version, 'products' => [$product->id], 'categories' => [$this->category->id]]))
            ->assertOk()
            ->assertJsonPath('changed', true)
            ->assertJsonPath('version', $newVersion)
            ->assertJsonPath("products.{$product->id}.badge", '-25%')
            ->assertJsonPath("products.{$product->id}.from.price", 3000)
            ->assertJsonPath("products.{$product->id}.from.original", 4000)
            ->assertJsonPath("products.{$product->id}.variants.{$product->variants->first()->id}.percent", 25)
            ->assertJsonStructure(['categories' => [$this->category->id => ['price', 'original']]]);
    }

    public function test_deleted_products_drop_out_and_listing_signatures_track_added_products(): void
    {
        $live = app(LiveUpdateService::class);
        $product = $this->product(['Standard' => 4000]);
        $listing = 'category:Gift Boxes';
        $before = $live->listingSignature($listing);

        $this->product(['Standard' => 5000]);
        $this->assertNotSame($before, $live->listingSignature($listing));

        $product->delete();
        $this->getJson(route('live', ['v' => 'stale', 'products' => [$product->id], 'listings' => [$listing]]))
            ->assertOk()
            ->assertJsonPath('products', [])
            ->assertJsonPath("listings.{$listing}", $live->listingSignature($listing));
    }

    public function test_signed_in_shoppers_get_their_unread_count_with_every_poll(): void
    {
        $this->getJson(route('live'))->assertOk()->assertJsonMissingPath('unread');   // guests

        $this->customer->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => ProductOnSaleNotification::class, 'data' => ['message' => 'Sale!'],
        ]);

        $this->actingAs($this->customer)->getJson(route('live', ['v' => app(LiveUpdateService::class)->version()]))
            ->assertOk()
            ->assertJsonPath('changed', false)
            ->assertJsonPath('unread', 1);
    }

    public function test_customers_are_notified_when_a_product_goes_on_sale_or_its_discount_grows(): void
    {
        $product = $this->product(['Small' => 2000, 'Large' => 3000]);

        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), ['percent' => 10]);

        Notification::assertSentTo($this->customer, ProductOnSaleNotification::class, function ($notification) use ($product) {
            return $notification->toDatabase($this->customer)['message'] === "Sale: {$product->name} is now 10% off!";
        });
        Notification::assertNotSentTo($this->admin, ProductOnSaleNotification::class);

        // a bigger discount is news again …
        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), ['percent' => 20]);
        Notification::assertSentToTimes($this->customer, ProductOnSaleNotification::class, 2);

        // … a smaller one or ending the sale is not
        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), ['percent' => 5]);
        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), []);
        Notification::assertSentToTimes($this->customer, ProductOnSaleNotification::class, 2);
    }

    public function test_a_single_variant_sale_or_a_balls_pack_sale_also_notifies(): void
    {
        $product = $this->product(['Small' => 2000, 'Large' => 3000]);
        $small = $product->variants->firstWhere('variant_name', 'Small');

        $this->actingAs($this->admin)->put(route('admin.products.variants.update', [$product, $small]), [
            'variant_name' => 'Small', 'price' => 2000, 'sale_price' => 1500,
            'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => 'active',
        ]);
        Notification::assertSentTo($this->customer, ProductOnSaleNotification::class, fn ($n) => $n->toDatabase($this->customer)['message'] === "Sale: {$product->name} is now up to 25% off!");

        $balls = Product::create(['name' => 'Live Test Balls ' . uniqid(), 'category_id' => $this->category->id]);
        $this->actingAs($this->admin)->put(route('admin.products.update', $balls), [
            'name' => $balls->name, 'category_id' => $balls->category_id, 'pricing_mode' => 'automatic',
            'base_price' => 1000, 'base_sale_price' => 800, 'max_qty' => 15,
        ])->assertSessionHasNoErrors();
        Notification::assertSentTo($this->customer, ProductOnSaleNotification::class, fn ($n) => $n->toDatabase($this->customer)['message'] === "Sale: {$balls->name} is now 20% off!");
    }

    public function test_storefront_pages_carry_the_live_hooks(): void
    {
        $product = $this->product(['Standard' => 4000]);

        $this->get(route('shop'))->assertOk()
            ->assertSee("const LIVE_URL = '/live';", false)
            ->assertSee('data-live-listing="shop"', false)
            ->assertSee('data-live-product="' . $product->id . '"', false);

        $this->get(route('gift-boxes'))->assertOk()->assertSee('data-live-listing="category:Gift Boxes"', false);
        $this->get(route('root'))->assertOk()->assertSee('data-live-category="' . $this->category->id . '"', false);

        $this->get(route('product-item', $product))->assertOk()
            ->assertSee('const LIVE_PAGE_STRUCTURE  = "' . app(LiveUpdateService::class)->structure($product->fresh('variants')) . '"', false)
            ->assertSee('data-variant-id', false);
    }

    public function test_order_and_refund_changes_move_only_that_customers_orders_version(): void
    {
        $live    = app(LiveUpdateService::class);
        $other   = User::factory()->create();
        $order   = $this->orderFor($this->customer);
        $mine    = $live->ordersVersion($this->customer->id);
        $theirs  = $live->ordersVersion($other->id);

        $this->actingAs($this->admin)->put(route('admin.orders.update', $order), ['order_status' => 'shipped'])->assertRedirect();
        $afterShipping = $live->ordersVersion($this->customer->id);
        $this->assertNotSame($mine, $afterShipping, 'an admin status change moves the customer\'s version');
        $this->assertSame($theirs, $live->ordersVersion($other->id), 'other customers are untouched');

        $refund = \App\Models\RefundRequest::create([
            'order_item_id' => $order->items->first()->id, 'amount' => 4000, 'bank_details' => 'Test bank', 'reason' => 'Test', 'requested_at' => now(),
        ]);
        $afterRequest = $live->ordersVersion($this->customer->id);
        $this->actingAs($this->admin)->put(route('admin.refund-requests.update', $refund), ['status' => 'approved']);
        $this->assertNotSame($afterRequest, $live->ordersVersion($this->customer->id), 'a refund decision moves it too');

        $this->actingAs($this->customer)->getJson(route('live', ['v' => $live->version()]))
            ->assertOk()
            ->assertJsonPath('orders_version', $live->ordersVersion($this->customer->id));
    }

    public function test_order_pages_mark_the_sections_that_update_live(): void
    {
        $order = $this->orderFor($this->customer);

        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertOk()->assertSee('data-live-region="order-detail"', false);
        $this->actingAs($this->customer)->get(route('track-order'))->assertOk()->assertSee('data-live-region="track-recent-orders"', false);
        $this->actingAs($this->customer)->get(route('profile'))->assertOk()
            ->assertSee('data-live-region="profile-overview"', false)
            ->assertSee('data-live-region="profile-orders"', false);
    }

    public function test_live_checks_never_write_the_session_cookie(): void
    {
        // Sessions live in a cookie here; a background check that wrote it could put an
        // older session back over a login or form submit ("419 Page Expired").
        $this->actingAs($this->customer);

        $this->assertEmpty($this->get(route('live'))->headers->getCookies());
        $this->assertEmpty($this->get(route('profile'), ['X-Live-Refresh' => '1'])->headers->getCookies());
        $this->assertNotEmpty($this->get(route('profile'))->headers->getCookies(), 'normal pages still keep the session');
    }

    public function test_expired_forms_return_to_the_page_with_a_message_instead_of_419(): void
    {
        \Illuminate\Support\Facades\Route::post('/_expired-form', fn () => throw new \Illuminate\Session\TokenMismatchException())->middleware('web');

        $this->from(route('admin.products.index'))->post('/_expired-form', ['name' => 'Kept'])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('error', "Your session had expired, so that change wasn't saved. Please try again.")
            ->assertSessionHasInput('name', 'Kept');

        $this->postJson('/_expired-form')->assertStatus(419);   // background requests handle it themselves
    }

    public function test_the_discount_dialog_saves_in_the_background_and_gets_the_new_row_values(): void
    {
        $product = $this->product(['Small' => 2000, 'Large' => 3000]);

        $this->actingAs($this->admin)->postJson(route('admin.products.sale', $product), ['percent' => 35])
            ->assertOk()
            ->assertExactJson([
                'message' => "35% off applied to {$product->name}.",
                'discount' => '-35%', 'percent' => 35, 'price_min' => 1300, 'price_max' => 1950,
            ]);

        $this->actingAs($this->admin)->postJson(route('admin.products.sale', $product), [])
            ->assertOk()
            ->assertJsonPath('discount', null)
            ->assertJsonPath('price_min', 2000);

        $this->actingAs($this->admin)->postJson(route('admin.products.sale', $product), ['percent' => 150])
            ->assertStatus(422)->assertJsonValidationErrors('percent');
    }

    private function orderFor(User $user): \App\Models\Order
    {
        $product = $this->product(['Standard' => 4000]);
        $order = \App\Models\Order::create([
            'user_id' => $user->id, 'order_type' => 'normal', 'shipping_name' => 'Live Test', 'shipping_phone' => '+923001234567',
            'shipping_address' => 'Test street', 'total_amount' => 4000, 'order_date' => now(), 'order_status' => 'confirmed',
            'payment_method' => 'credit_card', 'payment_status' => 'paid', 'tracking_number' => 'RVL-LIVETEST',
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $product->variants->first()->id, 'qty' => 1, 'price' => 4000]);

        return $order->load('items');
    }

    /** @param array<string, float> $prices */
    private function product(array $prices): Product
    {
        $product = Product::create(['name' => 'Live Test Box ' . uniqid(), 'category_id' => $this->category->id]);

        foreach ($prices as $name => $price) {
            $product->variants()->create(['variant_name' => $name, 'price' => $price, 'min_qty' => 1, 'max_qty' => 5, 'stock' => 5, 'status' => 'active']);
        }

        return $product->load('variants');
    }
}
