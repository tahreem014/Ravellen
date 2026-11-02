<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The admin's Categories and Sub-categories tabs: adding, renaming and deleting each, the
 * warnings for what the storefront finds by name, deletion that never takes products with it,
 * and the product form's category / sub-category dropdowns. Rolled back.
 */
class AdminCategoryManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private string $page;      // the Categories tab
    private string $subPage;   // the Sub-categories tab

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin   = User::factory()->create(['is_admin' => true]);
        $this->page    = route('admin.categories.index');
        $this->subPage = route('admin.sub-categories.index');
    }

    private function category(?string $name = null): Category
    {
        return Category::create(['name' => $name ?? 'Test Category ' . uniqid()]);
    }

    private function subCategory(Category $category, ?string $name = null): SubCategory
    {
        return $category->subCategories()->create(['name' => $name ?? 'Test Sub ' . uniqid()]);
    }

    private function productIn(Category $category, ?SubCategory $sub = null): Product
    {
        $product = Product::create([
            'name'            => 'Test Product ' . uniqid(),
            'category_id'     => $category->id,
            'sub_category_id' => $sub?->id,
        ]);
        $product->variants()->create(['variant_name' => 'Standard', 'price' => 1500, 'status' => 'active']);

        return $product;
    }

    /** Requests as the admin, sent from the Categories tab... */
    private function admin(): static
    {
        return $this->actingAs($this->admin)->from($this->page);
    }

    /** ...or from the Sub-categories tab, which is where a redirect back lands. */
    private function subAdmin(): static
    {
        return $this->actingAs($this->admin)->from($this->subPage);
    }

    // ── The two tabs ───────────────────────────────────────────────────────────

    public function test_only_admins_reach_the_category_pages(): void
    {
        foreach ([$this->page, $this->subPage] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $this->actingAs(User::factory()->create(['is_admin' => false]));

        foreach ([$this->page, $this->subPage] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_categories_and_sub_categories_are_separate_tabs(): void
    {
        $tab = fn (string $url, string $label, bool $active) => '#class="nav-link' . ($active ? ' active' : ' ') . '"\s+href="' . preg_quote($url, '#') . '">' . $label . '</a>#';

        $onCategories = $this->actingAs($this->admin)->get($this->page)->assertOk()->getContent();
        $this->assertMatchesRegularExpression($tab($this->page, 'Categories', true), $onCategories);
        $this->assertMatchesRegularExpression($tab($this->subPage, 'Sub-categories', false), $onCategories);

        $onSubCategories = $this->actingAs($this->admin)->get($this->subPage)->assertOk()->getContent();
        $this->assertMatchesRegularExpression($tab($this->page, 'Categories', false), $onSubCategories);
        $this->assertMatchesRegularExpression($tab($this->subPage, 'Sub-categories', true), $onSubCategories);

        // and both are on the other admin pages too
        $this->actingAs($this->admin)->get(route('admin.products.index'))->assertOk()
            ->assertSee('>Categories</a>', false)->assertSee('>Sub-categories</a>', false);
    }

    public function test_the_categories_tab_lists_categories_only_and_each_can_be_edited_and_deleted(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category, 'Only On The Other Tab');
        $this->subCategory($category);
        $this->category();   // one with no sub-categories at all
        Category::firstOrCreate(['name' => 'Gift Boxes']);

        $html = $this->actingAs($this->admin)->get($this->page)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<th>Category</th>\s*<th>Sub-categories</th>\s*<th>Products</th>#', $html);
        $this->assertStringContainsString($category->name, $html);
        $this->assertStringNotContainsString($sub->name, $html);   // sub-categories live on their own tab

        // a count that opens that category's sub-categories
        $this->assertStringContainsString('href="' . route('admin.sub-categories.index', ['category' => $category->id]) . '"', $html);

        // every category has Edit and Delete controls, built-in ones included
        $this->assertSame(Category::count(), preg_match_all('#<button[^>]*data-edit-category#', $html));
        $this->assertSame(Category::count(), preg_match_all('#<form[^>]*action="[^"]*/admin/categories/\d+"#', $html));
        $this->assertStringNotContainsString('data-edit-sub-category', $html);
    }

    public function test_the_sub_categories_tab_is_a_table_with_the_category_first_and_each_row_can_be_edited_and_deleted(): void
    {
        $category = $this->category('Zed Category');
        $first    = $this->subCategory($category, 'Zed First');
        $this->category('Zed Empty Category');   // no sub-categories, so no row here
        Category::firstOrCreate(['name' => 'Gift Boxes']);

        $html = $this->actingAs($this->admin)->get($this->subPage)->assertOk()->getContent();

        // Category is the first column and Sub-category the second
        $this->assertMatchesRegularExpression('#<th>Category</th>\s*<th>Sub-category</th>\s*<th>Products</th>#', $html);
        $this->assertMatchesRegularExpression('#<td data-label="Category">Zed Category</td>\s*<td data-label="Sub-category"[^>]*>Zed First</td>#', $html);
        $this->assertStringNotContainsString('Zed Empty Category</td>', $html);

        // one row per sub-category, every one with Edit and Delete, built-in ones included
        $this->assertSame(SubCategory::count(), preg_match_all('#<td data-label="Category">#', $html));
        $this->assertSame(SubCategory::count(), preg_match_all('#<button[^>]*data-edit-sub-category#', $html));
        $this->assertSame(SubCategory::count(), preg_match_all('#<form[^>]*action="[^"]*/admin/sub-categories/\d+"#', $html));
        $this->assertStringNotContainsString('data-edit-category', $html);
    }

    public function test_the_sub_category_list_can_be_narrowed_to_one_category(): void
    {
        $mine  = $this->category();
        $other = $this->category();
        $inMine  = $this->subCategory($mine, 'In Mine ' . uniqid());
        $inOther = $this->subCategory($other, 'In Other ' . uniqid());

        $this->actingAs($this->admin)->get(route('admin.sub-categories.index', ['category' => $mine->id]))
            ->assertOk()->assertSee($inMine->name)->assertDontSee($inOther->name);

        // an unknown category is ignored rather than hiding everything
        $this->actingAs($this->admin)->get(route('admin.sub-categories.index', ['category' => 999999999]))
            ->assertOk()->assertSee($inMine->name)->assertSee($inOther->name);

        // narrowed to a category with none, it says so
        $empty = $this->category();
        $this->actingAs($this->admin)->get(route('admin.sub-categories.index', ['category' => $empty->id]))
            ->assertOk()->assertSee($empty->name . ' has no sub-categories yet');
    }

    public function test_a_rejected_save_reopens_its_dialog_with_what_the_admin_typed(): void
    {
        $category = $this->category();
        $taken    = Category::firstOrCreate(['name' => 'Gift Boxes']);

        // the dialogs always send their `form` and `editing_id` fields along
        $this->admin()->post(route('admin.categories.store'), ['form' => 'category', 'editing_id' => '', 'name' => $taken->name])->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->get($this->page)->assertSee('openCategoryModal({ id: "", name: "Gift Boxes" })', false);

        $sub = $this->subCategory($category);
        $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['form' => 'sub-category', 'editing_id' => (string) $sub->id, 'category_id' => '', 'name' => 'Half Typed'])
            ->assertSessionHasErrors('category_id');
        $this->actingAs($this->admin)->get($this->subPage)
            ->assertSee('openSubCategoryModal({', false)
            ->assertSee('id: "' . $sub->id . '", name: "Half Typed", categoryId: ""', false);   // blank, not null, so the select shows its placeholder
    }

    public function test_names_with_quotes_or_markup_are_escaped_on_both_pages(): void
    {
        $category = $this->category('Kid\'s "Best" <b>Toys</b>');
        $sub      = $this->subCategory($category, 'It\'s <i>new</i>');

        $this->actingAs($this->admin)->get($this->page)
            ->assertOk()
            ->assertDontSee('<b>Toys</b>', false)
            ->assertSee('Kid&#039;s &quot;Best&quot; &lt;b&gt;Toys&lt;/b&gt;', false)
            // inside the delete confirmation's JS string the quotes are escaped as well as the markup
            ->assertSee('Delete the category \&quot;Kid&#039;s \&quot;Best\&quot; &lt;b&gt;Toys&lt;\/b&gt;\&quot; and its 1 sub-category?', false);

        $this->actingAs($this->admin)->get($this->subPage)
            ->assertOk()
            ->assertDontSee('<i>new</i>', false)
            ->assertSee('It&#039;s &lt;i&gt;new&lt;/i&gt;', false)
            ->assertSee('Delete the sub-category \&quot;It&#039;s &lt;i&gt;new&lt;\/i&gt;\&quot;?', false);
    }

    public function test_viewing_the_pages_changes_nothing(): void
    {
        Category::firstOrCreate(['name' => 'Gift Boxes']);
        $before = [Category::count(), SubCategory::count(), Product::count()];

        $this->actingAs($this->admin)->get($this->page)->assertOk();
        $this->actingAs($this->admin)->get($this->subPage)->assertOk();

        $this->assertSame($before, [Category::count(), SubCategory::count(), Product::count()]);
    }

    // ── Categories ─────────────────────────────────────────────────────────────

    public function test_a_category_can_be_added(): void
    {
        $this->admin()->post(route('admin.categories.store'), ['name' => 'Fresh Category'])
            ->assertRedirect($this->page)->assertSessionHasNoErrors()->assertSessionHas('status', 'Category added.');

        $this->assertDatabaseHas('categories', ['name' => 'Fresh Category']);
    }

    public function test_category_names_are_required_and_unique_whatever_the_case(): void
    {
        $existing = $this->category('Plush Toys');

        $this->admin()->post(route('admin.categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->admin()->post(route('admin.categories.store'), ['name' => 'plush toys'])->assertSessionHasErrors('name');

        $this->assertSame(1, Category::where('name', 'Plush Toys')->count());
        $this->assertTrue(Category::whereKey($existing->id)->exists());
    }

    public function test_a_category_can_be_renamed_and_keeps_its_products_and_sub_categories(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $product  = $this->productIn($category, $sub);

        $this->admin()->put(route('admin.categories.update', $category), ['name' => 'Renamed Category'])
            ->assertRedirect($this->page)->assertSessionHas('status', 'Category updated.');

        $this->assertSame('Renamed Category', $category->fresh()->name);
        $this->assertSame($category->id, $product->fresh()->category_id);
        $this->assertSame($sub->id, $product->fresh()->sub_category_id);
        $this->assertSame($category->id, $sub->fresh()->category_id);
    }

    public function test_renaming_a_category_to_a_taken_name_is_refused_but_keeping_its_own_name_is_fine(): void
    {
        $category = $this->category('Mine ' . uniqid());
        $other    = $this->category('Taken ' . uniqid());

        $this->admin()->put(route('admin.categories.update', $category), ['name' => $other->name])->assertSessionHasErrors('name');
        $this->admin()->put(route('admin.categories.update', $category), ['name' => $category->name])->assertSessionHasNoErrors();
    }

    public function test_the_built_in_categories_can_be_edited_but_not_deleted_while_they_hold_products(): void
    {
        foreach (Category::STOREFRONT_NAMES as $name) {
            $category = Category::firstOrCreate(['name' => $name]);
            $product  = $this->productIn($category);

            $this->admin()->put(route('admin.categories.update', $category), ['name' => $name . ' Renamed'])
                ->assertRedirect($this->page)->assertSessionHas('status', 'Category updated.');
            $this->assertSame($name . ' Renamed', $category->fresh()->name);

            $this->admin()->delete(route('admin.categories.destroy', $category))->assertSessionHas('error');
            $this->assertTrue($category->fresh()->exists);
            $this->assertTrue($product->fresh()->exists);
        }
    }

    public function test_the_shop_pages_keep_working_when_an_empty_built_in_category_is_deleted(): void
    {
        $scratch = $this->category();

        foreach (['Ceramic Items', 'BTS Merch'] as $name) {
            $category = Category::firstOrCreate(['name' => $name]);
            Product::where('category_id', $category->id)->update(['category_id' => $scratch->id, 'sub_category_id' => null]);

            $this->admin()->delete(route('admin.categories.destroy', $category))->assertSessionHas('status', 'Category deleted.');
            $this->assertFalse(Category::whereKey($category->id)->exists());
        }

        foreach (['root', 'shop', 'ceramic-items', 'bts-merch', 'gift-boxes'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_a_category_with_products_cannot_be_deleted_and_nothing_is_lost(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $product  = $this->productIn($category, $sub);

        $this->admin()->delete(route('admin.categories.destroy', $category))
            ->assertRedirect($this->page)
            ->assertSessionHas('error', fn ($message) => str_contains($message, '1 product is filed under it'));

        $this->assertTrue($category->fresh()->exists);
        $this->assertTrue($sub->fresh()->exists);
        $this->assertSame($category->id, $product->fresh()->category_id);
        $this->assertSame(1, $product->variants()->count());
    }

    public function test_a_product_in_a_sub_category_of_the_category_also_blocks_its_deletion(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $elsewhere = $this->category();
        // inconsistent on purpose: filed under another category but pointing at this one's sub-category
        $product = Product::create(['name' => 'Odd ' . uniqid(), 'category_id' => $elsewhere->id, 'sub_category_id' => $sub->id]);

        $this->admin()->delete(route('admin.categories.destroy', $category))->assertSessionHas('error');

        $this->assertTrue($category->fresh()->exists);
        $this->assertSame($sub->id, $product->fresh()->sub_category_id);
    }

    public function test_an_empty_category_is_deleted_together_with_its_empty_sub_categories(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);

        $this->admin()->delete(route('admin.categories.destroy', $category))
            ->assertRedirect($this->page)->assertSessionHas('status', 'Category deleted.');

        $this->assertFalse(Category::whereKey($category->id)->exists());
        $this->assertFalse(SubCategory::whereKey($sub->id)->exists());
    }

    public function test_the_model_itself_refuses_to_delete_a_category_that_has_products(): void
    {
        // products.category_id cascades in the database, so without this a stray ->delete() would
        // take every product (and their order lines) with it
        $category = $this->category();
        $product  = $this->productIn($category);

        $this->assertFalse($category->delete());
        $this->assertTrue($category->fresh()->exists);
        $this->assertTrue($product->fresh()->exists);
    }

    // ── Sub-categories ─────────────────────────────────────────────────────────

    public function test_a_sub_category_is_added_to_a_category(): void
    {
        $category = $this->category();

        $this->subAdmin()->post(route('admin.sub-categories.store'), ['category_id' => $category->id, 'name' => 'Fresh Sub'])
            ->assertRedirect($this->subPage)->assertSessionHasNoErrors()->assertSessionHas('status', 'Sub-category added.');

        $this->assertDatabaseHas('sub_categories', ['category_id' => $category->id, 'name' => 'Fresh Sub']);
    }

    public function test_a_sub_category_must_belong_to_an_existing_category(): void
    {
        $this->subAdmin()->post(route('admin.sub-categories.store'), ['name' => 'Orphan'])->assertSessionHasErrors('category_id');
        $this->subAdmin()->post(route('admin.sub-categories.store'), ['category_id' => 999999999, 'name' => 'Orphan'])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('sub_categories', ['name' => 'Orphan']);
    }

    public function test_sub_category_names_are_unique_within_a_category_only(): void
    {
        $category = $this->category();
        $other    = $this->category();
        $this->subCategory($category, 'Mini');

        $this->subAdmin()->post(route('admin.sub-categories.store'), ['category_id' => $category->id, 'name' => 'mini'])->assertSessionHasErrors('name');
        $this->subAdmin()->post(route('admin.sub-categories.store'), ['category_id' => $other->id, 'name' => 'Mini'])->assertSessionHasNoErrors();

        $this->assertSame(1, $category->subCategories()->count());
        $this->assertSame(1, $other->subCategories()->count());
    }

    public function test_a_sub_category_can_be_renamed_and_its_products_follow(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $product  = $this->productIn($category, $sub);

        $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['category_id' => $category->id, 'name' => 'Renamed Sub'])
            ->assertRedirect($this->subPage)->assertSessionHas('status', 'Sub-category updated.');

        $this->assertSame('Renamed Sub', $sub->fresh()->name);
        $this->assertSame($sub->id, $product->fresh()->sub_category_id);
    }

    public function test_an_empty_sub_category_can_move_to_another_category(): void
    {
        $from = $this->category();
        $to   = $this->category();
        $sub  = $this->subCategory($from);

        $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['category_id' => $to->id, 'name' => $sub->name])
            ->assertSessionHas('status');

        $this->assertSame($to->id, $sub->fresh()->category_id);
    }

    public function test_a_sub_category_with_products_cannot_move_to_another_category(): void
    {
        $from    = $this->category();
        $to      = $this->category();
        $sub     = $this->subCategory($from);
        $product = $this->productIn($from, $sub);

        $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['category_id' => $to->id, 'name' => $sub->name])
            ->assertSessionHas('error');

        $this->assertSame($from->id, $sub->fresh()->category_id);
        $this->assertSame($sub->id, $product->fresh()->sub_category_id);
    }

    public function test_the_built_in_sub_categories_can_be_renamed_and_deleted_once_no_product_uses_them(): void
    {
        foreach (SubCategory::STOREFRONT_NAMES as $categoryName => $names) {
            $category = Category::firstOrCreate(['name' => $categoryName]);

            foreach ($names as $name) {
                $sub = SubCategory::firstOrCreate(['category_id' => $category->id, 'name' => $name]);
                $product = $this->productIn($category, $sub);

                // in use: it can be renamed, but not deleted
                $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['category_id' => $category->id, 'name' => $name . ' Renamed'])
                    ->assertSessionHas('status', 'Sub-category updated.');
                $this->subAdmin()->delete(route('admin.sub-categories.destroy', $sub))->assertSessionHas('error');
                $this->assertTrue($sub->fresh()->exists);

                // once its products are moved off it, it can go
                Product::where('sub_category_id', $sub->id)->update(['sub_category_id' => null]);
                $this->subAdmin()->delete(route('admin.sub-categories.destroy', $sub))->assertSessionHas('status', 'Sub-category deleted.');
                $this->assertFalse(SubCategory::whereKey($sub->id)->exists());
                $this->assertTrue($product->fresh()->exists);
            }
        }
    }

    public function test_sub_categories_the_admin_adds_to_a_built_in_category_are_fully_editable(): void
    {
        $category = Category::firstOrCreate(['name' => 'Gift Boxes']);
        $sub      = $this->subCategory($category, 'Admin Added ' . uniqid());

        $this->subAdmin()->put(route('admin.sub-categories.update', $sub), ['category_id' => $category->id, 'name' => 'Admin Renamed'])
            ->assertSessionHas('status');
        $this->subAdmin()->delete(route('admin.sub-categories.destroy', $sub))->assertSessionHas('status', 'Sub-category deleted.');

        $this->assertFalse(SubCategory::whereKey($sub->id)->exists());
    }

    public function test_every_bts_bias_sub_category_is_locked(): void
    {
        // the model's list is a copy of the storefront's, so this catches the two drifting apart
        $this->assertEqualsCanonicalizing(
            array_values(HomeController::BTS_BIAS_SUB_CATEGORIES),
            SubCategory::STOREFRONT_NAMES['BTS Merch'],
        );
    }

    public function test_a_sub_category_with_products_cannot_be_deleted_and_its_products_keep_it(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $product  = $this->productIn($category, $sub);

        $this->subAdmin()->delete(route('admin.sub-categories.destroy', $sub))
            ->assertRedirect($this->subPage)
            ->assertSessionHas('error', fn ($message) => str_contains($message, '1 product uses it'));

        $this->assertTrue($sub->fresh()->exists);
        $this->assertSame($sub->id, $product->fresh()->sub_category_id);
    }

    public function test_an_empty_sub_category_is_deleted_without_touching_the_category(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $kept     = $this->subCategory($category);

        $this->subAdmin()->delete(route('admin.sub-categories.destroy', $sub))->assertSessionHas('status', 'Sub-category deleted.');

        $this->assertFalse(SubCategory::whereKey($sub->id)->exists());
        $this->assertTrue($kept->fresh()->exists);
        $this->assertTrue($category->fresh()->exists);
    }

    public function test_non_admins_cannot_change_categories(): void
    {
        $category = $this->category();
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($customer)->post(route('admin.categories.store'), ['name' => 'Sneaky'])->assertForbidden();
        $this->actingAs($customer)->put(route('admin.categories.update', $category), ['name' => 'Sneaky'])->assertForbidden();
        $this->actingAs($customer)->delete(route('admin.categories.destroy', $category))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.sub-categories.store'), ['category_id' => $category->id, 'name' => 'Sneaky'])->assertForbidden();

        $this->assertDatabaseMissing('categories', ['name' => 'Sneaky']);
        $this->assertTrue($category->fresh()->exists);
    }

    // ── The product form ───────────────────────────────────────────────────────

    public function test_the_product_forms_offer_the_databases_categories_with_their_own_sub_categories(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category, 'Zebra Sub ' . uniqid());
        $product  = $this->productIn($category, $sub);

        // each category's sub-categories are keyed by its id, and only that category's are under it
        $other = $this->category();
        $this->subCategory($other, 'Other Cat Sub ' . uniqid());

        foreach ([route('admin.products.create'), route('admin.products.edit', $product)] as $url) {
            $html = $this->actingAs($this->admin)->get($url)->assertOk()->assertSee($category->name)->assertSee($sub->name)->getContent();

            $this->assertMatchesRegularExpression('/"' . $category->id . '":\[\{"id":' . $sub->id . ',"name":"' . preg_quote($sub->name, '/') . '"\}\]/', $html);
        }

        // the edit form opens on the product's saved category and sub-category
        $html = $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->getContent();
        $this->assertMatchesRegularExpression('/<option value="' . $category->id . '"\s+selected/', $html);
        $this->assertStringContainsString('const wanted = ' . $sub->id . ';', $html);
    }

    public function test_a_product_can_be_filed_under_a_category_and_one_of_its_sub_categories(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Filed Product', 'category_id' => $category->id, 'sub_category_id' => $sub->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Filed Product', 'category_id' => $category->id, 'sub_category_id' => $sub->id]);
    }

    public function test_a_sub_category_of_a_different_category_is_refused_on_create_and_update(): void
    {
        $category = $this->category();
        $other    = $this->category();
        $foreign  = $this->subCategory($other);
        $product  = $this->productIn($category);

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Mismatched Product', 'category_id' => $category->id, 'sub_category_id' => $foreign->id,
        ])->assertSessionHasErrors('sub_category_id');
        $this->assertDatabaseMissing('products', ['name' => 'Mismatched Product']);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name, 'category_id' => $category->id, 'sub_category_id' => $foreign->id,
        ])->assertSessionHasErrors('sub_category_id');
        $this->assertNull($product->fresh()->sub_category_id);
    }

    public function test_a_sub_category_is_optional_and_can_be_cleared(): void
    {
        $category = $this->category();
        $sub      = $this->subCategory($category);
        $product  = $this->productIn($category, $sub);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name, 'category_id' => $category->id, 'sub_category_id' => '',
        ])->assertSessionHasNoErrors();

        $this->assertNull($product->fresh()->sub_category_id);
    }
}
