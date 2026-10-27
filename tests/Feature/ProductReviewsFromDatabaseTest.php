<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The pages the home cards open (Mystery Scoops, Mystery Balls, the BTS member pages, the static gift
 * box page) show the database's own reviews — nothing made up. Rolled back.
 */
class ProductReviewsFromDatabaseTest extends TestCase
{
    use DatabaseTransactions;

    private const MADE_UP_REVIEWERS = [
        'Sara K.', 'Hina R.', 'Zara M.', 'Ayesha T.', 'Maryam N.', 'Fatima B.',
        'Army4ever', 'PurpleYou', 'Sana A.', 'Noor F.', 'Rimsha K.', 'Laiba S.', 'Alina R.', 'Bisma T.', 'Hira N.',
    ];

    /** The product a generic page shows (same lookup as HomeController::genericProductPage), with no reviews yet. */
    private function cleanProduct(string $category, string $name): Product
    {
        $product = Product::with('variants')
            ->whereHas('category', fn ($q) => $q->where('name', $category))
            ->orderByRaw('name = ? desc', [$name])->orderBy('id')->first();

        if (! $product || $product->variants->isEmpty()) {
            $this->markTestSkipped("No {$category} product with variants in this database.");
        }

        Review::whereIn('product_variant_id', $product->variants->pluck('id'))->delete();

        return $product;
    }

    private function review(Product $product, string $comment, int $rating = 5): Review
    {
        $variant  = $product->variants->first();
        $customer = User::factory()->create(['name' => 'Real Reviewer ' . uniqid()]);
        $order    = Order::create([
            'user_id' => $customer->id, 'order_type' => 'normal', 'shipping_name' => 'T', 'shipping_address' => 'T', 'total_amount' => 1000,
            'order_date' => now(), 'order_status' => 'delivered', 'payment_method' => 'credit_card', 'payment_status' => 'paid', 'tracking_number' => 'RVL-REV-' . uniqid(),
        ]);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => 1, 'price' => 1000]);

        return Review::create(['user_id' => $customer->id, 'product_variant_id' => $variant->id, 'order_item_id' => $item->id, 'rating' => $rating, 'comments' => $comment]);
    }

    private function assertNoMadeUpReviewers($response): void
    {
        foreach (self::MADE_UP_REVIEWERS as $name) {
            $response->assertDontSee($name, false);
        }
    }

    public function test_mystery_scoops_and_balls_pages_show_real_reviews_only(): void
    {
        foreach (['/mystery-scoops' => ['Mystery Scoops', 'Original Mystery Scoop'], '/mystery-balls' => ['Mystery Balls', 'Original Mystery Balls']] as $url => [$category, $name]) {
            $product = $this->cleanProduct($category, $name);

            $empty = $this->get($url)->assertOk()->assertSee('No reviews yet.')->assertSee('(0 reviews)');
            $this->assertNoMadeUpReviewers($empty);

            $review = $this->review($product, 'Genuinely lovely, would order again ' . uniqid(), 4);
            $page   = $this->get($url)->assertOk()
                ->assertSee($review->comments)
                ->assertSee($review->user->name)
                ->assertSee('(1 review)')
                ->assertSee('4.0')
                ->assertDontSee('No reviews yet.');
            $this->assertNoMadeUpReviewers($page);
        }
    }

    public function test_a_bts_member_page_shows_that_members_own_product_reviews(): void
    {
        $scoop = Product::with('variants', 'subCategory')
            ->whereHas('category', fn ($q) => $q->where('name', 'BTS Merch'))
            ->whereHas('subCategory', fn ($q) => $q->where('name', 'SUGA'))
            ->where('name', 'like', '%Scoop%')->orderBy('id')->first();

        if (! $scoop || $scoop->variants->isEmpty()) {
            $this->markTestSkipped('No BTS Merch scoop for SUGA in this database.');
        }

        Review::whereIn('product_variant_id', $scoop->variants->pluck('id'))->delete();
        $this->assertNoMadeUpReviewers($this->get('/mystery-scoops?bias=suga')->assertOk()->assertSee('No reviews yet.'));

        $review = $this->review($scoop, 'The SUGA scoop was perfect ' . uniqid());
        $this->get('/mystery-scoops?bias=suga')->assertOk()->assertSee($review->comments)->assertSee('(1 review)');

        // another member's page doesn't borrow it
        $this->get('/mystery-scoops?bias=jimin')->assertOk()->assertDontSee($review->comments);
    }

    public function test_the_static_gift_box_page_has_no_reviews_rather_than_made_up_ones(): void
    {
        $page = $this->get('/gift-box?item=cooky-box')->assertOk()->assertSee('No reviews yet.');
        $this->assertNoMadeUpReviewers($page);
        $page->assertDontSee('Be the first to review this product!');
    }

    public function test_a_product_page_still_shows_its_reviews(): void
    {
        $product = Product::create(['name' => 'Review Test Box ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);
        $product->variants()->create(['variant_name' => 'Box', 'price' => 2500]);
        $review = $this->review($product->fresh('variants'), 'Lovely box ' . uniqid());

        $this->get(route('product-item', $product))->assertOk()->assertSee($review->comments)->assertSee('(1 review)');
    }
}
