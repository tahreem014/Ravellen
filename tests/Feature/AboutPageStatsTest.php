<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\LiveUpdateService;
use App\Services\StoreStatsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The About page's figures (happy customers, scoops sold, categories, average rating) are
 * counted from the database and refresh through GET /live?stats=1. The database already
 * holds real orders, so tests compare against what was there before. Rolled back.
 */
class AboutPageStatsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_story_badge_says_since_2026(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('Since 2026')
            ->assertDontSee('Since 2023');
    }

    public function test_the_page_shows_the_current_figures_and_marks_them_live(): void
    {
        $stats = app(StoreStatsService::class)->summary();
        $html  = preg_replace('/\s+/', ' ', $this->get(route('about'))->assertOk()->getContent());

        foreach ($stats as $key => $value) {
            $this->assertStringContainsString('data-live-stat="' . $key . '" style="color:#', $html);
            $this->assertMatchesRegularExpression('/data-live-stat="' . $key . '"[^>]*>\s*' . preg_quote($value, '/') . '\s*</', $html, "$key renders its live value");
        }
    }

    public function test_customers_count_only_those_with_a_delivered_order_once_each(): void
    {
        $stats  = app(StoreStatsService::class);
        $before = $stats->happyCustomers();
        $buyer  = User::factory()->create();

        $this->order($buyer, 'confirmed');
        $this->assertSame($before, $stats->happyCustomers(), 'an undelivered order is not a happy customer yet');

        $this->order($buyer, 'delivered');
        $this->assertSame($before + 1, $stats->happyCustomers());

        $this->order($buyer, 'delivered');
        $this->assertSame($before + 1, $stats->happyCustomers(), 'a second delivered order from the same person adds nobody');
    }

    public function test_scoops_sold_counts_paid_scoop_units_only(): void
    {
        $stats  = app(StoreStatsService::class);
        $before = $stats->scoopsSold();
        $buyer  = User::factory()->create();

        $this->order($buyer, 'processing', 'paid', 3, StoreStatsService::SCOOPS_CATEGORY);
        $this->assertSame($before + 3, $stats->scoopsSold());

        $this->order($buyer, 'processing', 'partially_paid', 2, StoreStatsService::SCOOPS_CATEGORY);
        $this->assertSame($before + 5, $stats->scoopsSold(), 'a pre-order with its advance paid is sold');

        $this->order($buyer, 'pending', 'pending', 4, StoreStatsService::SCOOPS_CATEGORY);
        $this->order($buyer, 'cancelled', 'paid', 6, StoreStatsService::SCOOPS_CATEGORY);
        $this->order($buyer, 'processing', 'paid', 7, 'Gift Boxes');
        $this->assertSame($before + 5, $stats->scoopsSold(), 'unpaid, cancelled and non-scoop orders are left out');
    }

    public function test_categories_and_average_rating_follow_the_database(): void
    {
        $stats = app(StoreStatsService::class);

        $categories = Category::count();
        Category::create(['name' => 'Fresh Category ' . uniqid()]);
        $this->assertSame(number_format($categories + 1), $stats->summary()['categories']);

        Review::query()->delete();
        $this->assertSame('—', $stats->summary()['rating'], 'no reviews yet: a dash, not a made-up score');

        $author = User::factory()->create();
        $this->review($author, 5);
        $this->assertSame('5.0', $stats->summary()['rating']);

        $this->review($author, 4);
        $this->assertSame('4.5', $stats->summary()['rating']);
    }

    public function test_live_endpoint_sends_the_figures_only_when_asked_and_with_every_poll(): void
    {
        $version = app(LiveUpdateService::class)->version();

        $this->getJson(route('live', ['v' => $version]))
            ->assertOk()->assertJsonMissingPath('stats');

        $before = $this->getJson(route('live', ['v' => $version, 'stats' => 1]))
            ->assertOk()
            ->assertJsonPath('changed', false)
            ->assertJsonStructure(['stats' => ['customers', 'scoops', 'categories', 'rating']])
            ->json('stats');

        $buyer = User::factory()->create();
        $this->order($buyer, 'delivered', 'paid', 5, StoreStatsService::SCOOPS_CATEGORY);
        $this->review($buyer, 1);

        // (the helper also creates a product, which moves the catalog version; the figures ride along regardless)
        $after = $this->getJson(route('live', ['v' => app(LiveUpdateService::class)->version(), 'stats' => 1]))
            ->assertOk()
            ->assertJsonPath('changed', false)
            ->json('stats');

        $this->assertSame(number_format(((int) str_replace(',', '', $before['customers'])) + 1), $after['customers']);
        $this->assertSame(number_format(((int) str_replace(',', '', $before['scoops'])) + 5), $after['scoops']);
        $this->assertNotSame($before['rating'], $after['rating']);
    }

    /** A review hangs off an order line, so each one gets a delivered order of its own. */
    private function review(User $user, int $rating): Review
    {
        $item = $this->order($user, 'delivered')->items->first();

        return Review::create([
            'user_id' => $user->id, 'order_item_id' => $item->id, 'product_variant_id' => $item->product_variant_id, 'rating' => $rating,
        ]);
    }

    private function order(User $user, string $status, string $payment = 'paid', int $qty = 1, string $category = 'Gift Boxes'): Order
    {
        $category = Category::firstOrCreate(['name' => $category]);
        $product  = Product::create(['name' => 'Stats Test ' . uniqid(), 'category_id' => $category->id]);
        $variant  = $product->variants()->create(['variant_name' => 'Item', 'price' => 1000, 'min_qty' => 1, 'max_qty' => 10, 'stock' => 50, 'status' => 'active']);

        $order = Order::create([
            'user_id' => $user->id, 'order_type' => 'normal', 'shipping_name' => 'Stats Test', 'shipping_phone' => '+923001234567',
            'shipping_address' => 'Test street', 'total_amount' => 1000 * $qty, 'order_date' => now(), 'order_status' => $status,
            'payment_method' => 'credit_card', 'payment_status' => $payment, 'tracking_number' => 'RVL-' . strtoupper(uniqid()),
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => $qty, 'price' => 1000]);

        return $order;
    }
}
