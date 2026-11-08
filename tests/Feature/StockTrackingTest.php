<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\User;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Stock follows the orders: placing one takes its quantities, cancelling gives them back, the last
 * unit makes the variant Out of stock by itself, and the storefront ("Only N left") and the admin
 * product list both see every change live. Rolled back after each test.
 */
class StockTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $buyer;
    private Category $category;
    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin    = User::factory()->create(['is_admin' => true]);
        $this->buyer    = User::factory()->create();
        $this->category = Category::firstOrCreate(['name' => 'Gift Boxes']);

        ShippingRate::create(['country' => 'Stockland', 'city' => null, 'amount' => 100, 'is_active' => true]);
        $this->address = Address::create([
            'user_id' => $this->buyer->id, 'name' => 'Stock Test', 'address' => 'Test street',
            'country' => 'Stockland', 'city' => 'Testville', 'phone' => '+923001234567', 'is_default' => true,
        ]);
    }

    // ── orders take stock ────────────────────────────────────────────────────

    public function test_placing_an_order_takes_its_quantity_out_of_the_stock(): void
    {
        $variant = $this->variant(stock: 20);

        $order = $this->order([[$variant, 3]]);

        $this->assertSame(17, $variant->fresh()->stock);
        $this->assertSame(3, $order->items->first()->stock_reserved, 'the line remembers what it holds');
        $this->assertSame(ProductVariant::STATUS_ACTIVE, $variant->fresh()->status);
    }

    public function test_an_order_cannot_take_more_than_is_left_and_a_rejected_order_takes_nothing(): void
    {
        $plenty = $this->variant(stock: 5, name: 'Plenty');
        $few    = $this->variant(stock: 2, name: 'Few');
        $ids    = [$this->addToCart($plenty, 1), $this->addToCart($few, 3)];

        $this->placeOrder($ids)->assertStatus(422)->assertJsonPath('success', false)
            ->assertSee('Only 2 left of', false);

        $this->assertSame([5, 2], [$plenty->fresh()->stock, $few->fresh()->stock], 'the other line was not charged either');
        $this->assertSame(0, Order::where('user_id', $this->buyer->id)->count());
        $this->assertSame(2, $this->buyer->cartItems()->count(), 'the cart is left as it was');
    }

    public function test_the_last_units_sell_the_variant_out_and_it_goes_out_of_stock_by_itself(): void
    {
        $variant = $this->variant(stock: 2);
        $product = $variant->product;

        $this->order([[$variant, 2]]);

        $variant->refresh();
        $this->assertSame([0, ProductVariant::STATUS_OUT_OF_STOCK], [$variant->stock, $variant->status]);
        $this->assertSame('Out of stock', $product->fresh('variants')->stockLabel());

        // nobody can add it to a cart or order it any more
        $this->actingAs($this->buyer)->postJson(route('cart.store'), ['product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => 1])
            ->assertStatus(422);
    }

    public function test_a_variant_only_goes_out_of_stock_when_it_was_on_sale_and_ran_out(): void
    {
        $preorder     = $this->variant(stock: 5, status: ProductVariant::STATUS_PRE_ORDER);
        $restocking   = $this->variant(stock: 5, status: ProductVariant::STATUS_RESTOCKING);
        $manualOut    = $this->variant(stock: 9, status: ProductVariant::STATUS_OUT_OF_STOCK);   // the admin's own choice, stock and all

        foreach ([$preorder, $restocking, $manualOut] as $variant) {
            $variant->update(['stock' => 0]);
        }
        $manualOut->update(['stock' => 4]);

        $this->assertSame(ProductVariant::STATUS_PRE_ORDER, $preorder->fresh()->status);
        $this->assertSame(ProductVariant::STATUS_RESTOCKING, $restocking->fresh()->status);
        $this->assertSame(ProductVariant::STATUS_OUT_OF_STOCK, $manualOut->fresh()->status, 'more stock alone does not put it back on sale');

        $active = $this->variant(stock: 3);
        $active->update(['price' => 1200]);   // a save that leaves the stock alone never moves the status
        $this->assertSame(ProductVariant::STATUS_ACTIVE, $active->fresh()->status);
        $active->update(['stock' => 0]);
        $this->assertSame(ProductVariant::STATUS_OUT_OF_STOCK, $active->fresh()->status);
    }

    public function test_pre_orders_never_take_or_give_back_stock(): void
    {
        $variant = $this->variant(stock: 3, status: ProductVariant::STATUS_PRE_ORDER);

        $order = $this->order([[$variant, 2]]);

        $this->assertSame(Order::TYPE_PRE_ORDER, $order->order_type);
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertNull($order->items->first()->stock_reserved);

        $this->cancel($order);
        $this->assertSame(3, $variant->fresh()->stock);
    }

    // ── cancelling gives stock back ──────────────────────────────────────────

    public function test_cancelling_an_order_gives_the_stock_back_once_and_brings_a_sold_out_variant_back(): void
    {
        $variant = $this->variant(stock: 2);
        $order   = $this->order([[$variant, 2]]);
        $this->assertSame(ProductVariant::STATUS_OUT_OF_STOCK, $variant->fresh()->status);

        $this->cancel($order);
        $variant->refresh();
        $this->assertSame([2, ProductVariant::STATUS_ACTIVE], [$variant->stock, $variant->status], 'the units are back and it is on sale again');

        $this->cancel($order);   // cancelling an already cancelled order changes nothing
        $this->assertSame(2, $variant->fresh()->stock);

        // moving it out of Cancelled takes the units again
        $this->actingAs($this->admin)->put(route('admin.orders.update', $order), ['order_status' => 'pending'])->assertRedirect();
        $variant->refresh();
        $this->assertSame([0, ProductVariant::STATUS_OUT_OF_STOCK], [$variant->stock, $variant->status]);
        $this->assertSame(2, $order->items()->first()->stock_reserved);
    }

    public function test_cancelling_an_order_placed_before_stock_was_tracked_adds_nothing(): void
    {
        $variant = $this->variant(stock: 10);
        $order = Order::create([
            'user_id' => $this->buyer->id, 'order_type' => 'normal', 'shipping_name' => 'Old', 'shipping_phone' => '+923001234567',
            'shipping_address' => 'Test street', 'total_amount' => 1000, 'order_date' => now(), 'order_status' => 'confirmed',
            'payment_method' => 'credit_card', 'payment_status' => 'paid', 'tracking_number' => 'RVL-OLD-' . uniqid(),
        ]);
        $order->items()->create(['product_id' => $variant->product_id, 'product_variant_id' => $variant->id, 'qty' => 4, 'price' => 1000]);

        $this->cancel($order);

        $this->assertSame(10, $variant->fresh()->stock, 'it never took stock, so it gives none back');
    }

    // ── the admin enters stock ───────────────────────────────────────────────

    public function test_entering_zero_stock_in_the_admin_takes_an_active_variant_off_sale(): void
    {
        $variant = $this->variant(stock: 8);
        $product = $variant->product;

        $this->actingAs($this->admin)->put(route('admin.products.variants.update', [$product, $variant]), [
            'variant_name' => 'Standard', 'price' => 1000, 'min_qty' => 1, 'max_qty' => 20, 'stock' => 0, 'status' => 'active',
        ])->assertRedirect();

        $this->assertSame([0, ProductVariant::STATUS_OUT_OF_STOCK], [$variant->fresh()->stock, $variant->fresh()->status]);
    }

    public function test_the_admin_form_can_stock_a_sold_out_variant_back_on_sale(): void
    {
        $variant = $this->variant(stock: 0);   // saved with no stock: out of stock from the start
        $product = $variant->product;
        $this->assertSame(ProductVariant::STATUS_OUT_OF_STOCK, $variant->fresh()->status);

        // what the edit form posts once the admin has typed the new stock (its script switches Status to Active)
        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name, 'category_id' => $product->category_id,
            'variants' => [$variant->id => ['variant_name' => 'Standard', 'price' => 1000, 'min_qty' => 1, 'max_qty' => 20, 'stock' => 12, 'status' => 'active']],
        ])->assertSessionHasNoErrors();

        $this->assertSame([12, ProductVariant::STATUS_ACTIVE], [$variant->fresh()->stock, $variant->fresh()->status]);
    }

    // ── storefront: "Only N left" ────────────────────────────────────────────

    public function test_the_low_stock_label_starts_at_fifteen(): void
    {
        $variant = $this->variant(stock: 16);
        $this->assertNull($variant->lowStockLabel());

        foreach ([15 => 'Only 15 left', 7 => 'Only 7 left', 1 => 'Only 1 left'] as $stock => $label) {
            $variant->update(['stock' => $stock]);
            $this->assertSame($label, $variant->fresh()->lowStockLabel());
        }

        $variant->update(['stock' => 0]);
        $this->assertNull($variant->fresh()->lowStockLabel(), 'sold out says Out of stock, not "Only 0 left"');
        $this->assertNull($this->variant(stock: 3, status: ProductVariant::STATUS_PRE_ORDER)->lowStockLabel(), 'a pre-order is not limited by stock');
    }

    public function test_the_product_page_carries_the_stock_note_and_the_live_feed_counts_it_down(): void
    {
        $variant = $this->variant(stock: 14);
        $product = $variant->product;
        $live    = app(LiveUpdateService::class);

        $this->get(route('product-item', $product))->assertOk()
            ->assertSee('data-stock-left', false)
            ->assertSee('const VARIANT_STOCK = {"' . $variant->id . '":14}', false);

        $version = $live->version();
        $this->order([[$variant, 3]]);
        $this->assertNotSame($version, $live->version(), 'an order moves the storefront version');

        $this->getJson(route('live', ['v' => $version, 'products' => [$product->id]]))->assertOk()
            ->assertJsonPath('changed', true)
            ->assertJsonPath("products.{$product->id}.variants.{$variant->id}.stock", 11);

        // and the page, opened again, starts from the new number
        $this->get(route('product-item', $product))->assertSee('const VARIANT_STOCK = {"' . $variant->id . '":11}', false);
    }

    public function test_the_live_feed_has_no_stock_figure_for_variants_stock_does_not_limit(): void
    {
        $variant = $this->variant(stock: 5, status: ProductVariant::STATUS_PRE_ORDER);

        $this->getJson(route('live', ['v' => 'old', 'products' => [$variant->product_id]]))
            ->assertOk()
            ->assertJsonPath("products.{$variant->product_id}.variants.{$variant->id}.stock", null);
    }

    // ── admin: live stock ────────────────────────────────────────────────────

    public function test_the_admin_product_list_shows_stock_and_the_live_feed_follows_every_order(): void
    {
        $variant = $this->variant(stock: 40);
        $product = $variant->product;

        $this->actingAs($this->admin)->get(route('admin.products.index'))->assertOk()
            ->assertSee('data-stock-cell="' . $product->id . '"', false)
            ->assertSee('40 in stock');

        $first = $this->stockFeed($product)
            ->assertJsonPath("products.{$product->id}.total", 40)
            ->assertJsonPath("products.{$product->id}.variants.{$variant->id}.stock", 40);

        $this->order([[$variant, 30]]);

        $second = $this->stockFeed($product)
            ->assertJsonPath("products.{$product->id}.total", 10)
            ->assertJsonPath("products.{$product->id}.variants.{$variant->id}.stock", 10);

        $this->assertNotSame($first->json("products.{$product->id}.signature"), $second->json("products.{$product->id}.signature"));
        $this->assertStringContainsString('10 in stock', $second->json("products.{$product->id}.html"));
        $this->assertStringContainsString('stock-low', $second->json("products.{$product->id}.html"), 'at 15 or fewer the cell turns amber');

        $this->order([[$variant, 10]]);
        $third = $this->stockFeed($product);
        $this->assertSame(0, $third->json("products.{$product->id}.total"));
        $this->assertSame(ProductVariant::STATUS_OUT_OF_STOCK, $third->json("products.{$product->id}.variants.{$variant->id}.status"));
        $this->assertStringContainsString('Out of stock', $third->json("products.{$product->id}.html"));
    }

    public function test_only_admins_reach_the_stock_feed_and_polling_leaves_the_session_cookie_alone(): void
    {
        $this->getJson(route('admin.products.stock'))->assertUnauthorized();
        $this->actingAs($this->buyer)->getJson(route('admin.products.stock'))->assertForbidden();

        $response = $this->actingAs($this->admin)->getJson(route('admin.products.stock', ['ids' => [999999999]]))->assertOk();
        $this->assertSame([], $response->headers->getCookies(), 'a background poll must not overwrite the admin\'s session cookie');
        $this->assertEquals((object) [], (object) $response->json('products'), 'a product that does not exist is simply missing');
    }

    public function test_the_edit_page_tells_the_live_poll_what_the_database_holds(): void
    {
        $variant = $this->variant(stock: 6);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $variant->product))->assertOk()
            ->assertSee('name="variants[' . $variant->id . '][stock]" value="6" data-synced="6"', false)
            ->assertSee('data-synced="active"', false)
            ->assertSee('const PRODUCT_ID = "' . $variant->product_id . '"', false)
            ->assertSee('admin\/products\/stock', false);   // @json escapes the slashes
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function variant(int $stock, string $status = ProductVariant::STATUS_ACTIVE, string $name = 'Standard'): ProductVariant
    {
        $product = Product::create(['name' => 'Stock Test ' . uniqid(), 'category_id' => $this->category->id]);

        $variant = $product->variants()->create([
            'variant_name' => $name, 'price' => 1000, 'min_qty' => 1, 'max_qty' => 20, 'stock' => $stock, 'status' => $status,
        ]);

        return $variant->setRelation('product', $product);
    }

    private function addToCart(ProductVariant $variant, int $qty): int
    {
        $this->actingAs($this->buyer)->postJson(route('cart.store'), [
            'product_id' => $variant->product_id, 'product_variant_id' => $variant->id, 'qty' => $qty,
        ])->assertCreated();

        return (int) $this->buyer->cartItems()->where('product_variant_id', $variant->id)->value('id');
    }

    private function placeOrder(array $cartItemIds)
    {
        return $this->actingAs($this->buyer)->postJson(route('orders.store'), [
            'shipping_name' => 'Stock Test', 'shipping_phone' => '+923001234567', 'shipping_address' => 'Test street',
            'payment_method' => 'credit_card', 'address_id' => $this->address->id, 'cart_item_ids' => $cartItemIds,
        ]);
    }

    /** @param array<int, array{0: ProductVariant, 1: int}> $lines */
    private function order(array $lines): Order
    {
        $ids = array_map(fn (array $line) => $this->addToCart($line[0], $line[1]), $lines);

        $response = $this->placeOrder($ids)->assertCreated();

        return Order::with('items')->where('uuid', $response->json('orders.0.uuid'))->firstOrFail();
    }

    /** What the admin's product pages poll for (placing an order logs the buyer in, so sign the admin back in). */
    private function stockFeed(Product $product)
    {
        return $this->actingAs($this->admin)->getJson(route('admin.products.stock', ['ids' => [$product->id]]))->assertOk();
    }

    private function cancel(Order $order): void
    {
        $this->actingAs($this->admin)->put(route('admin.orders.update', $order), ['order_status' => 'cancelled'])->assertRedirect();
    }
}
