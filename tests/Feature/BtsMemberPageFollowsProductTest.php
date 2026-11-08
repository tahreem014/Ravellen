<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Services\LiveUpdateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The BTS member pages (/mystery-scoops?bias=… and /mystery-balls?bias=…) show the product's own name, description,
 * photo and order limits, and each scoop size has its own Min / Max (the admin's per-size values), not fixed text
 * like "Max: 20". Rolled back.
 */
class BtsMemberPageFollowsProductTest extends TestCase
{
    use DatabaseTransactions;

    private function memberProduct(string $member, string $name, ?string $description = 'Admin written description'): Product
    {
        $bts = Category::firstOrCreate(['name' => 'BTS Merch']);
        Product::where('category_id', $bts->id)->update(['category_id' => Category::create(['name' => 'Scratch ' . uniqid()])->id, 'sub_category_id' => null]);
        $sub = SubCategory::firstOrCreate(['category_id' => $bts->id, 'name' => $member]);

        return Product::create(['name' => $name, 'category_id' => $bts->id, 'sub_category_id' => $sub->id, 'description' => $description]);
    }

    private function addSize(Product $product, string $name, int $price, int $min, int $max): ProductVariant
    {
        return $product->variants()->create(['variant_name' => $name, 'price' => $price, 'min_qty' => $min, 'max_qty' => $max, 'stock' => 5, 'status' => ProductVariant::STATUS_ACTIVE]);
    }

    public function test_each_scoop_size_carries_its_own_min_and_max(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Mystery Scoop');
        $this->addSize($scoop, 'Classic Scoop', 3500, 1, 5);
        $this->addSize($scoop, 'Premium Scoop', 6500, 2, 7);

        $html = $this->get(route('mystery-scoops', ['bias' => 'jimin']))->assertOk()->getContent();

        // the page opens on the first size: its own limits, and its minimum as the starting quantity
        $this->assertStringContainsString('id="scoop-min-qty">1</span>', $html);
        $this->assertStringContainsString('id="scoop-max-qty">5</span>', $html);
        $this->assertStringNotContainsString('Max: 20', $html);
        $this->assertStringNotContainsString('maximum 20', $html);

        // the script switches to the picked size's limits
        $this->assertStringContainsString('"min":1,"max":5', $this->compact($html));
        $this->assertStringContainsString('"min":2,"max":7', $this->compact($html));

        // ...and the size list tells every size's limits
        $this->assertStringContainsString('(min 1, max 5)', $html);
        $this->assertStringContainsString('(min 2, max 7)', $html);
    }

    public function test_a_scoop_whose_first_size_has_a_higher_minimum_starts_there(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Mystery Scoop');
        $this->addSize($scoop, 'Classic Scoop', 1000, 3, 6);

        $html = $this->get(route('mystery-scoops', ['bias' => 'jimin']))->assertOk()->getContent();

        $this->assertStringContainsString('id="scoop-qty-display">3</span>', $html);
        $this->assertStringContainsString('id="summary-qty">3</span>', $html);
        $this->assertStringContainsString('PKR 3,000', $html);   // 3 × 1,000 in the total
    }

    public function test_the_scoop_page_shows_the_products_own_name_description_and_photo(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Sparkle Scoop', 'Hand picked by the Ravellen team');
        $this->addSize($scoop, 'Classic Scoop', 3500, 1, 5);
        ProductImage::create(['product_id' => $scoop->id, 'image' => 'https://cdn.example.test/jimin-scoop.jpg']);

        $html = $this->get(route('mystery-scoops', ['bias' => 'jimin']))->assertOk()
            ->assertSee('Jimin Sparkle Scoop')
            ->assertSee('Hand picked by the Ravellen team')
            ->assertSee('https://cdn.example.test/jimin-scoop.jpg', false)
            ->assertDontSee('photo-1642761450221', false)   // the stock photo is only the no-photo fallback
            ->getContent();

        // the description shown is the product's, not the fixed blurb (which only waits in data-pi-fallback)
        preg_match('/<p[^>]*data-pi-description[^>]*>(.*?)<\/p>/s', $html, $description);
        $this->assertSame('Hand picked by the Ravellen team', trim($description[1] ?? ''));

        // a guest's cart line gets the product's name and photo too
        $this->assertMatchesRegularExpression('/\'bts-scoop\':\s*\{\s*name:\s*"Jimin Sparkle Scoop",\s*image:\s*"https:\\\\\/\\\\\/cdn\.example\.test\\\\\/jimin-scoop\.jpg"/', $html);
    }

    public function test_a_product_without_a_description_keeps_the_default_blurb(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Mystery Scoop', null);
        $this->addSize($scoop, 'Classic Scoop', 3500, 1, 5);

        $this->get(route('mystery-scoops', ['bias' => 'jimin']))->assertOk()
            ->assertSee('Exclusive themed mystery items for ARMY!');
    }

    public function test_the_balls_page_shows_the_products_own_details_and_pack_range(): void
    {
        $balls = $this->memberProduct('Jungkook', 'Jungkook Lucky Balls', 'Straight from the admin');
        foreach ([5, 10, 15] as $qty) {
            $balls->variants()->create(['variant_name' => 'Pack of ' . $qty, 'price' => $qty * 200, 'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => ProductVariant::STATUS_ACTIVE]);
        }
        $balls->update(['pricing_mode' => Product::PRICING_CUSTOM]);

        $this->get(route('mystery-balls', ['bias' => 'jungkook']))->assertOk()
            ->assertSee('Jungkook Lucky Balls')
            ->assertSee('Straight from the admin')
            ->assertSee('any quantity from 5 to 15 balls in increments of 5')   // the packs on offer, not "5 to 50"
            ->assertDontSee('5 to 50');
    }

    public function test_the_shops_product_page_follows_the_picked_sizes_limits_too(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Mystery Scoop');
        $this->addSize($scoop, 'Classic Scoop', 3500, 2, 5);
        $this->addSize($scoop, 'Premium Scoop', 6500, 1, 7);

        $html = $this->get(route('product-item', $scoop->slug))->assertOk()->getContent();

        $this->assertStringContainsString('id="pi-min-qty">2</span>', $html);
        $this->assertStringContainsString('id="pi-max-qty">5</span>', $html);
        $this->assertStringContainsString('"min_qty":1,"max_qty":7', $this->compact($html));
        $this->assertStringNotContainsString('maximum 20', $html);
    }

    public function test_live_updates_carry_each_sizes_limits(): void
    {
        $scoop   = $this->memberProduct('Jimin', 'Jimin Mystery Scoop');
        $classic = $this->addSize($scoop, 'Classic Scoop', 3500, 2, 5);

        $snapshot = app(LiveUpdateService::class)->products([$scoop->id])[$scoop->id];
        $this->assertSame([2, 5], [$snapshot['variants'][$classic->id]['min_qty'], $snapshot['variants'][$classic->id]['max_qty']]);

        $classic->update(['min_qty' => 1, 'max_qty' => 8]);

        $snapshot = app(LiveUpdateService::class)->products([$scoop->id])[$scoop->id];
        $this->assertSame([1, 8], [$snapshot['variants'][$classic->id]['min_qty'], $snapshot['variants'][$classic->id]['max_qty']]);
    }

    public function test_order_limits_are_never_below_one_or_inverted(): void
    {
        $scoop = $this->memberProduct('Jimin', 'Jimin Mystery Scoop');
        $odd   = $this->addSize($scoop, 'Odd Scoop', 1000, 0, 0);   // what an unset / legacy row can hold

        $this->assertSame([1, 1], [$odd->orderMin(), $odd->orderMax()]);

        $odd->update(['min_qty' => 4, 'max_qty' => 2]);
        $this->assertSame([4, 4], [$odd->fresh()->orderMin(), $odd->fresh()->orderMax()]);
    }

    /** The page with its whitespace squeezed out, so the JSON the script reads can be matched. */
    private function compact(string $html): string
    {
        return preg_replace('/\s+/', '', $html);
    }
}
