<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A variant's status (active, out of stock, restocking, pre-order, discontinued) set in the admin shows on
 * every product's storefront pages: the product page, the listing cards and the live updates. Rolled back.
 */
class VariantStatusStorefrontTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @param array<string, string> $variants variant name => status */
    private function product(array $variants, string $category = 'Gift Boxes'): Product
    {
        $product = Product::create(['name' => 'Status Test ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => $category])->id]);

        foreach ($variants as $name => $status) {
            $product->variants()->create(['variant_name' => $name, 'price' => 1000, 'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => $status]);
        }

        return $product->load('variants');
    }

    private function saveStatus(Product $product, ProductVariant $variant, string $status)
    {
        // what the admin page posts in the background: the whole row, status included
        return $this->actingAs($this->admin)
            ->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->put(route('admin.products.variants.update', [$product, $variant]), [
                'variant_name' => $variant->variant_name, 'price' => $variant->price, 'sale_price' => $variant->sale_price,
                'min_qty' => $variant->min_qty, 'max_qty' => $variant->max_qty, 'stock' => $variant->stock, 'status' => $status,
            ]);
    }

    public function test_the_admin_can_change_a_variants_status(): void
    {
        $product = $this->product(['Standard' => 'active']);
        $variant = $product->variants->first();

        foreach (['out_of_stock', 'restocking', 'pre_order', 'discontinued', 'active'] as $status) {
            $this->saveStatus($product, $variant, $status)->assertOk();
            $this->assertSame($status, $variant->fresh()->status);
        }

        $this->saveStatus($product, $variant, 'sold-out')->assertStatus(422);
        $this->assertSame('active', $variant->fresh()->status);
    }

    public function test_what_a_product_says_about_its_stock(): void
    {
        $this->assertNull($this->product([])->stockLabel());
        $this->assertNull($this->product(['A' => 'active', 'B' => 'out_of_stock'])->stockLabel());
        $this->assertNull($this->product(['A' => 'pre_order'])->stockLabel());
        $this->assertSame('Out of stock', $this->product(['A' => 'out_of_stock', 'B' => 'restocking'])->stockLabel());
        $this->assertSame('Restocking', $this->product(['A' => 'restocking'])->stockLabel());
        // a discontinued size is no longer offered, so the rest decides
        $this->assertNull($this->product(['A' => 'discontinued', 'B' => 'active'])->stockLabel());
        $this->assertSame('Out of stock', $this->product(['A' => 'discontinued', 'B' => 'out_of_stock'])->stockLabel());
        $this->assertSame('Unavailable', $this->product(['A' => 'discontinued'])->stockLabel());
    }

    public function test_the_product_page_cannot_order_an_out_of_stock_variant_and_hides_a_discontinued_one(): void
    {
        foreach (['Gift Boxes', 'Ceramic Items', 'BTS Gift Boxes', 'Mystery Scoops'] as $category) {   // every kind of product page
            $product = $this->product(['Small' => 'out_of_stock', 'Large' => 'active', 'Old' => 'discontinued'], $category);
            $url     = route('product-item', $product->slug);

            // opens on the variant that can be bought
            $page = $this->get($url)->assertOk();
            $page->assertSee('Out of stock')->assertDontSee('Old')->assertDontSee('id="pi-add-btn" disabled', false);

            $small = $product->variants->firstWhere('variant_name', 'Small');
            $this->assertSame('Out of stock', $small->unavailableLabel());
            $page->assertSee('"unavailable":"Out of stock"', false);

            // nothing left to buy
            $product->variants->firstWhere('variant_name', 'Large')->update(['status' => 'restocking']);
            $this->get($url)->assertOk()->assertSee('id="pi-add-btn" disabled', false)->assertSee('<strong data-pi-stock-label>Out of stock</strong>', false);
        }
    }

    public function test_listing_cards_show_a_badge_only_when_nothing_can_be_bought(): void
    {
        $product = $this->product(['Only' => 'active']);

        // The shop lists the real catalogue too, where some product may genuinely be sold out, so count the badges
        $badge  = '>Out of stock</span>';
        $badges = fn () => substr_count($this->get(route('shop'))->assertOk()->getContent(), $badge);
        $before = $badges();

        $product->variants->first()->update(['status' => 'out_of_stock']);
        $this->assertSame($before + 1, $badges());

        $product->variants->first()->update(['status' => 'pre_order']);
        $this->assertSame($before, $badges());
    }

    public function test_live_updates_carry_the_status(): void
    {
        $live    = app(LiveUpdateService::class);
        $product = $this->product(['Only' => 'active']);
        $variant = $product->variants->first();

        $structure = fn () => $live->structure($product->fresh('variants'));
        $before    = $structure();

        // active ↔ pre-order is patched in place; running out of stock re-renders the page
        $variant->update(['status' => 'pre_order']);
        $this->assertSame($before, $structure());

        $variant->update(['status' => 'out_of_stock']);
        $this->assertNotSame($before, $structure());
        $this->assertSame('Out of stock', $live->products([$product->id])[$product->id]['stock']);

        $variant->update(['status' => 'active']);
        $this->assertNull($live->products([$product->id])[$product->id]['stock']);
        $this->assertSame($before, $structure());
    }
}
