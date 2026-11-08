<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The "more than 4 same items will be upgraded" note that the BTS scoop card carries also shows on the
 * regular Mystery Scoops product page — same wording, same spot (under the description, above the price).
 * Rolled back.
 */
class MysteryScoopDuplicateNoteTest extends TestCase
{
    use DatabaseTransactions;

    private const NOTE = 'More than 4 same items will be upgraded to prevent duplicates. Specify in comments if you prefer otherwise.';

    private function product(string $category): Product
    {
        $product = Product::create([
            'name'        => 'Note Test ' . uniqid(),
            'description' => 'A test description',
            'category_id' => Category::firstOrCreate(['name' => $category])->id,
        ]);

        foreach (['Classic' => 3500, 'Premium' => 6500] as $name => $price) {
            $product->variants()->create(['variant_name' => $name, 'price' => $price, 'min_qty' => 1, 'max_qty' => 20, 'stock' => 5, 'status' => 'active']);
        }

        return $product;
    }

    public function test_the_mystery_scoops_page_shows_the_note_under_the_description_and_above_the_sizes(): void
    {
        $page = $this->get(route('product-item', $this->product('Mystery Scoops')->slug))->assertOk();
        $page->assertSee(self::NOTE);

        $html = $page->getContent();
        $card = strpos($html, 'class="text-muted small mb-3" data-pi-description');

        $this->assertNotFalse($card);
        $this->assertGreaterThan($card, strpos($html, self::NOTE), 'the note sits below the description');
        $this->assertLessThan(strpos($html, 'Select Variant'), strpos($html, self::NOTE), 'the note sits above the size picker');
    }

    public function test_other_product_pages_do_not_show_the_note(): void
    {
        foreach (['Mystery Balls', 'Gift Boxes', 'Ceramic Items'] as $category) {
            $this->get(route('product-item', $this->product($category)->slug))->assertOk()->assertDontSee(self::NOTE);
        }
    }
}
