<?php

namespace Tests\Feature;

use App\Models\AddToCart;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Sale prices and the Mystery Balls pricing modes, from the admin form through
 * the product pages, cart and order. Everything runs inside a transaction that
 * is rolled back, so the database is left exactly as it was.
 */
class ProductPricingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Category $ballsCategory;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->ballsCategory = Category::firstOrCreate(['name' => 'Mystery Balls']);
    }

    public function test_automatic_pricing_builds_every_pack_from_the_five_ball_price(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Pricing Test Balls',
            'category_id' => $this->ballsCategory->id,
            'pricing_mode' => 'automatic',
            'base_price' => 1000,
            'base_sale_price' => 900,
            'max_qty' => 30,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $product = Product::where('name', 'Pricing Test Balls')->latest('id')->firstOrFail();

        $this->assertSame(Product::PRICING_AUTOMATIC, $product->pricing_mode);
        $this->assertSame([
            ['qty' => 5,  'price' => 900.0,  'original' => 1000.0],
            ['qty' => 10, 'price' => 1800.0, 'original' => 2000.0],
            ['qty' => 15, 'price' => 2700.0, 'original' => 3000.0],
            ['qty' => 20, 'price' => 3600.0, 'original' => 4000.0],
            ['qty' => 25, 'price' => 4500.0, 'original' => 5000.0],
            ['qty' => 30, 'price' => 5400.0, 'original' => 6000.0],
        ], $this->packSummary($product));
    }

    public function test_custom_pricing_keeps_each_quantitys_own_price(): void
    {
        $product = $this->customBallsProduct([5 => 1000, 10 => 1900, 20 => 3600]);

        $this->assertSame(Product::PRICING_CUSTOM, $product->pricing_mode);
        $this->assertSame([
            ['qty' => 5,  'price' => 1000.0, 'original' => null],
            ['qty' => 10, 'price' => 1900.0, 'original' => null],
            ['qty' => 20, 'price' => 3600.0, 'original' => null],
        ], $this->packSummary($product));
    }

    public function test_switching_modes_reprices_packs_in_place_and_retires_removed_quantities(): void
    {
        $product = $this->customBallsProduct([5 => 1000, 10 => 1900, 15 => 2750, 20 => 3600]);
        $ids = $product->packVariants()->mapWithKeys(fn ($v) => [$v->packSize() => $v->id]);

        // Someone has the 20-ball pack in their cart, so it can't simply be deleted.
        AddToCart::create(['user_id' => $this->admin->id, 'product_id' => $product->id, 'product_variant_id' => $ids[20], 'qty' => 1]);

        $this->updateProduct($product, ['pricing_mode' => 'automatic', 'base_price' => 1200, 'max_qty' => 10]);

        $product->refresh()->load('variants');
        $this->assertSame([
            ['qty' => 5,  'price' => 1200.0, 'original' => null],
            ['qty' => 10, 'price' => 2400.0, 'original' => null],
        ], $this->packSummary($product));
        $this->assertSame($ids[5], $product->packVariants()->first()->id, 'existing packs are repriced, not recreated');
        $this->assertNull(ProductVariant::find($ids[15]), 'an unused pack is deleted');
        $this->assertSame(ProductVariant::STATUS_DISCONTINUED, ProductVariant::find($ids[20])->status, 'a pack in use is discontinued');

        // Adding 20 back brings the discontinued pack back with its new price.
        $this->updateProduct($product, ['pricing_mode' => 'custom', 'packs' => [
            ['qty' => 5, 'price' => 1200],
            ['qty' => 20, 'price' => 4000, 'sale_price' => 3500],
        ]]);

        $pack20 = ProductVariant::find($ids[20]);
        $this->assertSame(ProductVariant::STATUS_ACTIVE, $pack20->status);
        $this->assertSame(3500.0, $pack20->effectivePrice());
    }

    public function test_pricing_form_rejects_invalid_prices_and_quantities(): void
    {
        $product = $this->customBallsProduct([5 => 1000]);

        $this->updateProduct($product, ['pricing_mode' => 'custom', 'packs' => [
            ['qty' => 5, 'price' => 1000, 'sale_price' => 1000],
            ['qty' => 12, 'price' => 2000],
            ['qty' => 5, 'price' => 3000],
        ]])->assertSessionHasErrors(['packs.0.sale_price', 'packs.1.qty', 'packs.2.qty']);

        $this->updateProduct($product, ['pricing_mode' => 'automatic', 'max_qty' => 50])
            ->assertSessionHasErrors('base_price');

        $this->updateProduct($product, ['pricing_mode' => 'automatic', 'base_price' => 1000, 'base_sale_price' => 1200, 'max_qty' => 50])
            ->assertSessionHasErrors('base_sale_price');

        $this->assertSame(1000.0, $product->fresh('variants')->packVariants()->first()->effectivePrice(), 'nothing was saved');
    }

    public function test_variant_sale_price_and_whole_product_sale(): void
    {
        $product = $this->standardProduct(['Small' => 2000, 'Large' => 3000]);
        [$small, $large] = $product->variants->sortBy('price')->values()->all();

        $this->actingAs($this->admin)->put(route('admin.products.variants.update', [$product, $small]), [
            'variant_name' => 'Small', 'price' => 2000, 'sale_price' => 1500,
            'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => 'active',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1500.0, $small->fresh()->effectivePrice());

        $this->actingAs($this->admin)->put(route('admin.products.variants.update', [$product, $small]), [
            'variant_name' => 'Small', 'price' => 2000, 'sale_price' => 2500,
            'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => 'active',
        ])->assertSessionHasErrors('sale_price');

        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), ['percent' => 15]);
        $this->assertSame(1700.0, $small->fresh()->effectivePrice());
        $this->assertSame(2550.0, $large->fresh()->effectivePrice());

        $this->actingAs($this->admin)->post(route('admin.products.sale', $product), []);
        $this->assertFalse($small->fresh()->hasDiscount());
        $this->assertFalse($large->fresh()->hasDiscount());
    }

    public function test_pack_rows_only_take_stock_and_status_edits(): void
    {
        $product = $this->customBallsProduct([5 => 1000]);
        $pack = $product->packVariants()->first();

        $this->actingAs($this->admin)->put(route('admin.products.variants.update', [$product, $pack]), [
            'variant_name' => 'Free balls', 'price' => 1, 'stock' => 7, 'status' => 'active',
        ])->assertSessionHasNoErrors();

        $pack->refresh();
        $this->assertSame('Pack of 5', $pack->variant_name);
        $this->assertSame(1000.0, (float) $pack->price);
        $this->assertSame(7, (int) $pack->stock);
    }

    public function test_cart_and_order_charge_the_sale_price_worked_out_on_the_server(): void
    {
        $buyer = User::factory()->create();
        $mug   = $this->standardProduct(['Mug' => 2000]);
        $mug->variants->first()->update(['sale_price' => 1500]);
        $balls = $this->customBallsProduct([5 => 1000, 10 => 1900]);
        $pack10 = $balls->packVariants()->first(fn ($v) => $v->packSize() === 10);
        $pack10->update(['sale_price' => 1700]);

        $this->actingAs($buyer)->postJson(route('cart.store'), [
            'product_id' => $mug->id, 'product_variant_id' => $mug->variants->first()->id, 'qty' => 2,
        ])->assertCreated()->assertJsonPath('cartItem.unitPrice', 1500)->assertJsonPath('cartItem.originalPrice', 2000);

        $this->actingAs($buyer)->postJson(route('cart.store'), [
            'product_id' => $balls->id, 'product_variant_id' => $pack10->id, 'qty' => 1,
        ])->assertCreated()->assertJsonPath('cartItem.unitPrice', 1700);

        // A variant must belong to the product, and every line needs one.
        $this->actingAs($buyer)->postJson(route('cart.store'), [
            'product_id' => $mug->id, 'product_variant_id' => $pack10->id, 'qty' => 1,
        ])->assertStatus(422);
        $this->actingAs($buyer)->postJson(route('cart.store'), ['product_id' => $mug->id, 'qty' => 1])->assertStatus(422);

        $address = $this->addressFor($buyer);

        $response = $this->actingAs($buyer)->postJson(route('orders.store'), [
            'shipping_name' => 'Pricing Test', 'shipping_phone' => '+923001234567', 'shipping_address' => 'Test street',
            'payment_method' => 'credit_card', 'address_id' => $address->id,
            'cart_item_ids' => $buyer->cartItems()->pluck('id')->all(),
        ])->assertCreated();

        $order = Order::with('items')->where('uuid', $response->json('orders.0.uuid'))->firstOrFail();
        $this->assertEqualsWithDelta(1500 * 2 + 1700 + 250, (float) $order->total_amount, 0.001);
        $this->assertEqualsCanonicalizing([1500.0, 1700.0], $order->items->map(fn ($item) => (float) $item->price)->all());
    }

    public function test_order_refuses_cart_lines_that_are_no_longer_for_sale(): void
    {
        $buyer = User::factory()->create();
        $product = $this->standardProduct(['Mug' => 2000]);
        $variant = $product->variants->first();
        $line = AddToCart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => 1]);
        $variant->update(['status' => ProductVariant::STATUS_DISCONTINUED]);

        $this->actingAs($buyer)->postJson(route('orders.store'), [
            'shipping_name' => 'Pricing Test', 'shipping_phone' => '+923001234567', 'shipping_address' => 'Test street',
            'payment_method' => 'credit_card', 'address_id' => $this->addressFor($buyer)->id,
            'cart_item_ids' => [$line->id],
        ])->assertStatus(422);

        $this->assertSame(0, Order::where('user_id', $buyer->id)->count());
    }

    public function test_product_pages_follow_the_pricing_mode_and_show_discounts(): void
    {
        $automatic = $this->customBallsProduct([5 => 1000]);
        $this->updateProduct($automatic, ['pricing_mode' => 'automatic', 'base_price' => 1000, 'base_sale_price' => 800, 'max_qty' => 20]);

        $this->get(route('product-item', $automatic->fresh()))
            ->assertOk()
            ->assertSee('Price per 5 balls')
            ->assertSee('PKR 800 <s class="price-was"', false)
            ->assertSee('{"qty":20,', false)
            ->assertDontSee('class="size-btn balls-pack-btn', false);

        $custom = $this->customBallsProduct([5 => 1000, 10 => 1900]);
        $this->get(route('product-item', $custom))
            ->assertOk()
            ->assertSee('class="size-btn balls-pack-btn', false)
            ->assertSee('Price for <span class="balls-price-qty">5</span> balls', false);

        $mug = $this->standardProduct(['Mug' => 2000]);
        $mug->variants->first()->update(['sale_price' => 1500]);
        $this->get(route('shop'))->assertOk()->assertSee('From PKR 1,500 <s class="price-was"', false);
        $this->get(route('product-item', $mug))->assertOk()->assertSee('PKR 2,000</s>', false);

        $this->get(route('mystery-balls'))->assertOk()->assertSee('const BALL_PACKS = [{"qty":', false);
        $this->get(route('mystery-scoops'))->assertOk()->assertSee('const SCOOP_OPTIONS = [{"id":', false);
    }

    public function test_discounted_products_show_a_percentage_badge_on_their_images(): void
    {
        $boxes = Category::firstOrCreate(['name' => 'Gift Boxes']);
        $mug = $this->standardProduct(['Small' => 1500, 'Large' => 2000]);
        $mug->update(['category_id' => $boxes->id]);
        [$small, $large] = $mug->variants->sortBy('price')->values()->all();
        $small->update(['sale_price' => 1200]);   // 20% off
        $large->update(['sale_price' => 1600]);   // 20% off
        $plain = $this->standardProduct(['Plain' => 900]);
        $plain->update(['category_id' => $boxes->id]);

        $this->assertSame('-20%', $mug->fresh('variants')->discountLabel());
        $this->assertNull($plain->fresh('variants')->discountLabel());

        $badge = '>-20%</span>';
        $this->get(route('shop'))->assertOk()->assertSee($badge, false);
        $this->get(route('gift-boxes'))->assertOk()->assertSee($badge, false);
        $this->get(route('product-item', $mug))->assertOk()->assertSee('id="discount-badge"', false)->assertSee($badge, false);
        // the plain product's own badge is hidden, but its related-products row shows the mug's
        $this->get(route('product-item', $plain))->assertOk()
            ->assertSee('display:none;"></span>', false)
            ->assertSee($badge, false);

        $buyer = User::factory()->create();
        $buyer->wishLists()->create(['product_id' => $mug->id]);
        $this->actingAs($buyer)->get(route('wish-list'))->assertOk()->assertSee($badge, false);

        // different discounts per variant (or a variant at full price) read "Up to"
        $large->update(['sale_price' => null]);
        $this->assertSame('Up to -20%', $mug->fresh('variants')->discountLabel());

        // Mystery Balls packs carry their own percentage for the live product-page badge
        $balls = $this->customBallsProduct([5 => 1000, 10 => 2000]);
        $balls->packVariants()->first()->update(['sale_price' => 850]);
        $this->assertSame(15, $balls->fresh('variants')->packOptions()[0]['percent']);
        $this->get(route('product-item', $balls))->assertOk()->assertSee('>-15%</span>', false);
    }

    public function test_discount_can_be_set_from_the_product_list_and_the_edit_page(): void
    {
        $mug  = $this->standardProduct(['Small' => 2000, 'Large' => 3000]);
        $list = route('admin.products.index', ['q' => $mug->name]);

        $this->actingAs($this->admin)->get($list)
            ->assertOk()
            ->assertSee('<th>Discount</th>', false)
            ->assertSee('Set discount')
            ->assertSee('data-action="' . route('admin.products.sale', $mug) . '"', false);

        // Applying it from the list returns to the list
        $this->actingAs($this->admin)->from($list)->post(route('admin.products.sale', $mug), ['percent' => 20])
            ->assertRedirect($list)
            ->assertSessionHas('status', "20% off applied to {$mug->name}.");
        $this->actingAs($this->admin)->get($list)->assertSee('-20%')->assertSee('Change');

        // The edit page leads with the Discount box, filled in with the running discount
        $this->actingAs($this->admin)->get(route('admin.products.edit', $mug))
            ->assertOk()
            ->assertSeeInOrder(['Discount', '-20%', 'Update discount', 'End discount', 'Product Details'])
            ->assertSee('name="percent" value="20"', false);

        $this->actingAs($this->admin)->from(route('admin.products.edit', $mug))->post(route('admin.products.sale', $mug), [])
            ->assertRedirect(route('admin.products.edit', $mug));
        $this->assertNull($mug->fresh('variants')->discountLabel());

        // Automatic Mystery Balls: every pack stays a multiple of the 5-ball sale price
        $balls = $this->customBallsProduct([5 => 1000]);
        $this->updateProduct($balls, ['pricing_mode' => 'automatic', 'base_price' => 1050, 'max_qty' => 15]);
        $this->actingAs($this->admin)->post(route('admin.products.sale', $balls), ['percent' => 15]);
        // 1050 × 0.85 = 892.5 → PKR 893 per 5 balls
        $this->assertSame([893.0, 1786.0, 2679.0], $balls->fresh('variants')->packVariants()->map(fn ($v) => $v->effectivePrice())->all());
    }

    public function test_admin_product_forms_render_for_each_mode(): void
    {
        $this->actingAs($this->admin)->get(route('admin.products.create'))
            ->assertOk()->assertSee('Mystery Balls · Automatic');

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->customBallsProduct([5 => 1000])))
            ->assertOk()->assertSee('Packs')->assertSee('name="packs[0][price]"', false);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->standardProduct(['Mug' => 2000])))
            ->assertOk()->assertSee('name="sale_price"', false)->assertSee('Apply discount');

        $this->actingAs($this->admin)->get(route('admin.products.index'))->assertOk();
    }

    /** @param array<int, float> $prices ball count => price */
    private function customBallsProduct(array $prices): Product
    {
        $product = Product::create(['name' => 'Pricing Test Balls ' . uniqid(), 'category_id' => $this->ballsCategory->id]);

        $this->updateProduct($product, [
            'pricing_mode' => 'custom',
            'packs' => collect($prices)->map(fn ($price, $qty) => ['qty' => $qty, 'price' => $price])->values()->all(),
        ])->assertSessionHasNoErrors();

        return $product->refresh()->load('variants');
    }

    /** @param array<string, float> $prices variant name => price */
    private function standardProduct(array $prices): Product
    {
        $product = Product::create(['name' => 'Pricing Test Mug ' . uniqid(), 'category_id' => $this->ballsCategory->id]);

        foreach ($prices as $name => $price) {
            $product->variants()->create([
                'variant_name' => $name, 'price' => $price, 'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => 'active',
            ]);
        }

        return $product->load('variants');
    }

    private function updateProduct(Product $product, array $pricing)
    {
        return $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'category_id' => $product->category_id,
        ] + $pricing);
    }

    private function packSummary(Product $product): array
    {
        return $product->fresh('variants')->packVariants()->map(fn (ProductVariant $variant) => [
            'qty' => $variant->packSize(),
            'price' => $variant->effectivePrice(),
            'original' => $variant->hasDiscount() ? (float) $variant->price : null,
        ])->all();
    }

    private function addressFor(User $buyer): Address
    {
        ShippingRate::create(['country' => 'Pricingland', 'city' => null, 'amount' => 250, 'is_active' => true]);

        return Address::create([
            'user_id' => $buyer->id, 'name' => 'Pricing Test', 'address' => 'Test street',
            'country' => 'Pricingland', 'city' => 'Testville', 'phone' => '+923001234567', 'is_default' => true,
        ]);
    }
}
