<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The BTS Merch page (and the shop's sub-category filter) only offer what is really in the database: a member
 * with products shows exactly those, a member with none says "Coming soon". Nothing is made up per member. Rolled back.
 */
class BtsMerchMemberProductsTest extends TestCase
{
    use DatabaseTransactions;

    /** BTS Merch with every member sub-category and no products (the products are moved aside, not deleted). */
    private function emptyBtsMerch(): Category
    {
        $bts = Category::firstOrCreate(['name' => 'BTS Merch']);
        Product::where('category_id', $bts->id)->update(['category_id' => Category::create(['name' => 'Scratch ' . uniqid()])->id, 'sub_category_id' => null]);

        foreach (['Jungkook', 'V (Taehyung)', 'Jimin', 'RM (Namjoon)', 'Jin', 'J-Hope', 'SUGA', 'OT7', 'BT21'] as $member) {
            SubCategory::firstOrCreate(['category_id' => $bts->id, 'name' => $member]);
        }

        return $bts;
    }

    private function memberProduct(Category $bts, string $member, string $name, array $variant): Product
    {
        $sub     = SubCategory::where(['category_id' => $bts->id, 'name' => $member])->firstOrFail();
        $product = Product::create(['name' => $name, 'category_id' => $bts->id, 'sub_category_id' => $sub->id, 'description' => 'Real ' . $name . ' description']);
        $product->variants()->create($variant + ['status' => ProductVariant::STATUS_ACTIVE]);

        return $product;
    }

    /** The HTML of one member's panel on the BTS Merch page. */
    private function panel(string $bias): string
    {
        $html = $this->get(route('bts-merch'))->assertOk()->getContent();

        // from this member's panel to the next one (or the Borahae banner after the last)
        $from = explode('data-bias-products="' . $bias . '"', $html, 2)[1] ?? '';

        return preg_split('/data-bias-products="|borahae-banner/', $from, 2)[0];
    }

    public function test_a_member_without_products_says_coming_soon_instead_of_made_up_cards(): void
    {
        $bts = $this->emptyBtsMerch();
        $this->memberProduct($bts, 'Jungkook', 'Jungkook Mystery Balls', ['variant_name' => 'Pack of 5', 'price' => 1800]);

        foreach (['taehyung', 'jimin', 'namjoon', 'jin', 'jhope', 'suga', 'ot7', 'bt21'] as $bias) {
            $panel = $this->panel($bias);

            $this->assertStringContainsString('Coming Soon', $panel, $bias);
            $this->assertStringNotContainsString('bts-card', $panel, $bias);
            $this->assertStringNotContainsString('Order Mystery', $panel, $bias);
        }
    }

    public function test_a_member_shows_only_the_products_they_have_with_real_data(): void
    {
        $bts = $this->emptyBtsMerch();
        // lower-case "balls" like the admin typed it; Jungkook has no Scoop
        $balls = $this->memberProduct($bts, 'Jungkook', 'jungkook mystery balls', ['variant_name' => 'Pack of 5', 'price' => 1800]);

        $panel = $this->panel('jungkook');

        $this->assertStringContainsString('data-product="' . $balls->id . '"', $panel);
        $this->assertStringContainsString('jungkook mystery balls', $panel);
        $this->assertStringContainsString('Real jungkook mystery balls description', $panel);
        $this->assertStringContainsString('PKR 1,800', $panel);                 // its own price, not a default
        $this->assertStringContainsString(route('mystery-balls', ['bias' => 'jungkook']), $panel);
        $this->assertStringNotContainsString('Order Mystery Scoop', $panel);    // no Scoop product, no Scoop card
        $this->assertStringNotContainsString('Coming Soon', $panel);
    }

    public function test_a_member_with_both_shows_both_cards(): void
    {
        $bts = $this->emptyBtsMerch();
        $this->memberProduct($bts, 'SUGA', 'SUGA Mystery Scoop', ['variant_name' => 'Classic Scoop', 'price' => 2750]);
        $this->memberProduct($bts, 'SUGA', 'SUGA Mystery Balls', ['variant_name' => 'Pack of 5', 'price' => 1200]);

        $panel = $this->panel('suga');

        $this->assertStringContainsString('Order Mystery Scoop', $panel);
        $this->assertStringContainsString('Order Mystery Balls', $panel);
        $this->assertStringContainsString('PKR 2,750', $panel);
        $this->assertStringContainsString('PKR 1,200', $panel);
    }

    public function test_a_members_product_page_says_coming_soon_until_they_have_one(): void
    {
        $bts = $this->emptyBtsMerch();
        $this->memberProduct($bts, 'Jungkook', 'Jungkook Mystery Balls', ['variant_name' => 'Pack of 5', 'price' => 1800]);

        $this->get(route('mystery-scoops', ['bias' => 'namjoon']))->assertOk()
            ->assertSee("Namjoon's Mystery Scoop is on its way")
            ->assertDontSee('Order Mystery');

        $this->get(route('mystery-balls', ['bias' => 'taehyung']))->assertOk()
            ->assertSee("Taehyung's Mystery Balls are on their way");

        // Jungkook does have Balls, so that page is the product page, not "coming soon"
        $this->get(route('mystery-balls', ['bias' => 'jungkook']))->assertOk()
            ->assertDontSee('on their way');
    }

    public function test_the_shop_flags_a_sub_category_without_products_as_coming_soon(): void
    {
        $bts = $this->emptyBtsMerch();
        $this->memberProduct($bts, 'Jungkook', 'Jungkook Mystery Balls', ['variant_name' => 'Pack of 5', 'price' => 1800]);

        $jungkook = SubCategory::where(['category_id' => $bts->id, 'name' => 'Jungkook'])->first();
        $jimin    = SubCategory::where(['category_id' => $bts->id, 'name' => 'Jimin'])->first();

        $html = $this->get(route('shop'))->assertOk()->getContent();

        // the empty member's pill is flagged and has its own panel; the member with a product has neither
        $this->assertMatchesRegularExpression('/data-sub="' . $jimin->id . '"\s+data-parent="' . $bts->id . '"\s+data-coming-soon="1"/', $html);
        $this->assertStringContainsString('data-coming-soon-for-sub="' . $jimin->id . '"', $html);
        $this->assertDoesNotMatchRegularExpression('/data-sub="' . $jungkook->id . '"[^>]*data-coming-soon/', $html);
        $this->assertStringNotContainsString('data-coming-soon-for-sub="' . $jungkook->id . '"', $html);
    }
}
