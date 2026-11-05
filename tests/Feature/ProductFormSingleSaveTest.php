<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The admin Add / Edit product pages are one form with one Save button at the end: details, pricing,
 * the discount, every variant and the media are saved together — change one thing or everything.
 * Database changes are rolled back and every file written to public/images/products is deleted again.
 */
class ProductFormSingleSaveTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Category $category;

    /** @var string[] */
    private array $writtenFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->category = Category::firstOrCreate(['name' => 'Gift Boxes']);
    }

    protected function tearDown(): void
    {
        foreach ($this->writtenFiles as $file) {
            @unlink(public_path('images/products/' . $file));
        }

        parent::tearDown();
    }

    private function product(array $variants = ['Small' => 1000, 'Large' => 2000]): Product
    {
        $product = Product::create(['name' => 'Single Save ' . uniqid(), 'description' => 'Original', 'category_id' => $this->category->id]);

        foreach ($variants as $name => $price) {
            $product->variants()->create(['variant_name' => $name, 'price' => $price, 'min_qty' => 1, 'max_qty' => 5, 'stock' => 5, 'status' => 'active']);
        }

        return $product->load('variants');
    }

    /** The form exactly as the edit page renders it, so a test only has to change what it is about. */
    private function formFor(Product $product): array
    {
        $product->load('variants');

        return [
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'variants' => $product->variants->mapWithKeys(fn (ProductVariant $variant) => [$variant->id => [
                'variant_name' => $variant->variant_name, 'price' => $variant->price, 'sale_price' => $variant->sale_price,
                'min_qty' => $variant->min_qty, 'max_qty' => $variant->max_qty, 'stock' => $variant->stock, 'status' => $variant->status,
            ]])->all(),
            'new_variants' => [['variant_name' => '', 'price' => '', 'sale_price' => '', 'min_qty' => 1, 'max_qty' => 1, 'stock' => 0, 'status' => 'active']],
            'discount_percent' => $product->variants->map->discountPercent()->filter()->max(),
        ];
    }

    private function save(Product $product, array $form, array $extra = [])
    {
        return $this->actingAs($this->admin)->put(route('admin.products.update', $product), $form + $extra);
    }

    private function remember(Product $product): void
    {
        $media = $product->fresh()->images;
        $this->writtenFiles = array_merge($this->writtenFiles, $media->pluck('image')->merge($media->pluck('video'))->filter()->all());
    }

    public function test_each_page_is_one_form_with_one_save_button_at_the_end(): void
    {
        $product = $this->product();

        $edit = $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk();
        $html = $edit->getContent();
        $content = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        $this->assertSame(1, substr_count($content, '<form '));
        $this->assertSame(1, substr_count($content, 'type="submit"'));
        $edit->assertSee('Update Product')
            ->assertSee('name="variants[' . $product->variants[0]->id . '][price]"', false)
            ->assertSee('name="discount_percent"', false)
            ->assertSee('name="media[]"', false);
        foreach (['Save Changes', 'Save media', 'Apply discount', 'End discount', 'variants.update'] as $oldButton) {
            $edit->assertDontSee($oldButton);
        }
        // the button is the last thing in the form
        $this->assertGreaterThan(strrpos($content, 'name="media[]"'), strpos($content, 'Update Product'));

        $create = $this->actingAs($this->admin)->get(route('admin.products.create'))->assertOk();
        $html = $create->getContent();
        $content = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        $this->assertSame(1, substr_count($content, '<form '));
        $this->assertSame(1, substr_count($content, 'type="submit"'));
        $create->assertSee('Save Product')->assertSee('name="new_variants[0][variant_name]"', false)->assertSee('name="media[]"', false);
    }

    public function test_changing_everything_at_once_saves_everything(): void
    {
        $product = $this->product();
        [$small, $large] = [$product->variants[0], $product->variants[1]];
        $keep = $product->images()->create(['image' => 'keep-' . uniqid() . '.png']);
        $drop = $product->images()->create(['image' => 'drop-' . uniqid() . '.png']);
        file_put_contents(public_path('images/products/' . $drop->image), 'x');
        $this->writtenFiles[] = $drop->image;

        $form = $this->formFor($product);
        $form['name'] = 'Renamed Product';
        $form['description'] = 'New words';
        $form['variants'][$small->id]['price'] = 1500;
        $form['variants'][$small->id]['stock'] = 12;
        $form['variants'][$small->id]['status'] = 'out_of_stock';
        $form['new_variants'][0] = ['variant_name' => 'Giant', 'price' => 5000, 'sale_price' => '', 'min_qty' => 1, 'max_qty' => 2, 'stock' => 3, 'status' => 'pre_order'];

        $this->save($product, $form, [
            'remove_variants' => [$large->id],
            'remove_media' => [$drop->id],
            'media' => [UploadedFile::fake()->image('added.jpg', 300, 300)],
        ])->assertRedirect(route('admin.products.edit', $product))->assertSessionHas('status', 'Product updated.')->assertSessionHasNoErrors();

        $product = $product->fresh(['variants', 'images']);
        $this->remember($product);

        $this->assertSame('Renamed Product', $product->name);
        $this->assertSame('New words', $product->description);

        $small = $product->variants->firstWhere('id', $small->id);
        $this->assertEqualsWithDelta(1500, (float) $small->price, 0.001);
        $this->assertSame(12, $small->stock);
        $this->assertSame('out_of_stock', $small->status);

        $this->assertNull($product->variants->firstWhere('id', $large->id), 'the removed variant is gone');
        $giant = $product->variants->firstWhere('variant_name', 'Giant');
        $this->assertNotNull($giant);
        $this->assertSame(['pre_order', 3, 2], [$giant->status, $giant->stock, $giant->max_qty]);
        $this->assertCount(2, $product->variants);

        $this->assertCount(2, $product->images);   // the kept one and the new one
        $this->assertNotNull($product->images->firstWhere('id', $keep->id));
        $this->assertNull($product->images->firstWhere('id', $drop->id));
        $this->assertFileDoesNotExist(public_path('images/products/' . $drop->image));
    }

    public function test_changing_one_thing_changes_only_that(): void
    {
        $product = $this->product();
        [$small, $large] = [$product->variants[0], $product->variants[1]];
        $large->update(['sale_price' => 1500]);   // a per-variant sale the form shows and posts back untouched

        $form = $this->formFor($product);
        $form['variants'][$small->id]['status'] = 'restocking';

        $this->save($product, $form)->assertSessionHasNoErrors();

        $product = $product->fresh('variants');
        $this->assertSame('restocking', $product->variants->firstWhere('id', $small->id)->status);
        $this->assertSame('Single Save', substr($product->name, 0, 11));
        $this->assertSame('Original', $product->description);
        $this->assertSame(2, $product->variants->count());
        $this->assertEqualsWithDelta(1500, (float) $product->variants->firstWhere('id', $large->id)->sale_price, 0.001);
        $this->assertNull($product->variants->firstWhere('id', $small->id)->sale_price);
        $this->assertSame(0, $product->images()->count());
    }

    public function test_the_discount_box_only_counts_when_it_was_changed(): void
    {
        $product = $this->product();

        // typed in: every variant goes on sale at once
        $form = $this->formFor($product);
        $form['discount_percent'] = 25;
        $this->save($product, $form)->assertSessionHasNoErrors();
        $this->assertSame([750.0, 1500.0], $product->fresh('variants')->variants->map->effectivePrice()->sort()->values()->all());

        // saved again with the box as the page showed it: nothing is re-applied, so a variant's own sale price edit stands
        $form = $this->formFor($product->fresh());
        $form['variants'][$product->variants[0]->id]['sale_price'] = 900;
        $this->save($product, $form)->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(900, $product->fresh('variants')->variants->firstWhere('id', $product->variants[0]->id)->effectivePrice(), 0.001);

        // cleared: the sale ends everywhere
        $form = $this->formFor($product->fresh());
        $form['discount_percent'] = '';
        $this->save($product, $form)->assertSessionHasNoErrors();
        $this->assertSame([1000.0, 2000.0], $product->fresh('variants')->variants->map->effectivePrice()->sort()->values()->all());

        // a form that doesn't carry the box at all (an older client) leaves the sale alone
        $product->variants[0]->update(['sale_price' => 800]);
        $form = $this->formFor($product->fresh());
        unset($form['discount_percent']);
        $this->save($product, $form)->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(800, $product->fresh('variants')->variants->firstWhere('id', $product->variants[0]->id)->effectivePrice(), 0.001);
    }

    public function test_one_invalid_field_saves_nothing(): void
    {
        $product = $this->product();
        $small = $product->variants[0];

        $form = $this->formFor($product);
        $form['name'] = 'Should Not Stick';
        $form['variants'][$small->id]['stock'] = 99;
        $form['variants'][$product->variants[1]->id]['sale_price'] = 5000;   // above its 2000 price
        $form['new_variants'][0] = ['variant_name' => 'Half a row', 'price' => '', 'min_qty' => 1, 'max_qty' => 1, 'stock' => 0, 'status' => 'active'];

        $this->save($product, $form, ['remove_variants' => [$small->id]])
            ->assertSessionHasErrors(['variants.' . $product->variants[1]->id . '.sale_price', 'new_variants.0.price']);

        $product = $product->fresh('variants');
        $this->assertNotSame('Should Not Stick', $product->name);
        $this->assertSame(5, $product->variants->firstWhere('id', $small->id)->stock);
        $this->assertCount(2, $product->variants);
    }

    public function test_only_this_products_own_variants_can_be_changed(): void
    {
        $product = $this->product();
        $other = $this->product(['Other' => 700]);
        $stranger = $other->variants[0];

        $form = $this->formFor($product);
        $form['variants'][$stranger->id] = ['variant_name' => 'Hijacked', 'price' => 1, 'min_qty' => 1, 'max_qty' => 1, 'stock' => 0, 'status' => 'discontinued'];

        $this->save($product, $form, ['remove_variants' => [$stranger->id]])->assertSessionHasNoErrors();

        $stranger->refresh();
        $this->assertSame('Other', $stranger->variant_name);
        $this->assertSame('active', $stranger->status);
    }

    public function test_balls_packs_save_their_stock_and_status_with_the_pricing(): void
    {
        $balls = Category::firstOrCreate(['name' => 'Mystery Balls']);
        $product = Product::create(['name' => 'Packs Single Save ' . uniqid(), 'category_id' => $balls->id]);

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name, 'category_id' => $balls->id, 'pricing_mode' => 'automatic', 'base_price' => 1000, 'max_qty' => 15,
        ])->assertSessionHasNoErrors();

        $product = $product->fresh('variants');
        $ten = $product->variants->first(fn ($variant) => $variant->packSize() === 10);

        // the page of a balls product posts each pack's stock and status next to its pricing
        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name' => $product->name, 'category_id' => $balls->id,
            'pricing_mode' => 'automatic', 'base_price' => 1200, 'max_qty' => 15,
            'variants' => $product->variants->mapWithKeys(fn ($variant) => [$variant->id => ['stock' => $variant->id === $ten->id ? 7 : 5, 'status' => $variant->id === $ten->id ? 'out_of_stock' : 'active']])->all(),
            'new_variants' => [['variant_name' => 'Sneaky', 'price' => 1, 'min_qty' => 1, 'max_qty' => 1, 'stock' => 1, 'status' => 'active']],
        ])->assertSessionHasNoErrors();

        $product = $product->fresh('variants');
        $ten = $product->variants->first(fn ($variant) => $variant->packSize() === 10);
        $this->assertEqualsWithDelta(2400, (float) $ten->price, 0.001);   // the new 5-ball price, from the same save
        $this->assertSame([7, 'out_of_stock'], [$ten->stock, $ten->status]);
        $this->assertSame(3, $product->variants->count());                 // no "Sneaky" variant: packs come from Pricing
    }

    public function test_the_add_page_saves_the_product_with_its_variants_and_media_in_one_go(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Brand New ' . uniqid(),
            'description' => 'All at once',
            'category_id' => $this->category->id,
            'new_variants' => [
                0 => ['variant_name' => 'Mini', 'price' => 900, 'sale_price' => 800, 'min_qty' => 1, 'max_qty' => 4, 'stock' => 10, 'status' => 'active'],
                1 => ['variant_name' => '', 'price' => '', 'min_qty' => 1, 'max_qty' => 1, 'stock' => 0, 'status' => 'active'],   // blank: ignored
                2 => ['variant_name' => 'Maxi', 'price' => 2500, 'min_qty' => 1, 'max_qty' => 4, 'stock' => 2, 'status' => 'pre_order'],
            ],
            'media' => [UploadedFile::fake()->image('first.jpg', 300, 300), UploadedFile::fake()->image('second.png', 300, 300)],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $product = Product::with(['variants', 'images'])->where('description', 'All at once')->latest('id')->firstOrFail();
        $this->remember($product);

        $this->assertEqualsCanonicalizing(['Mini', 'Maxi'], $product->variants->pluck('variant_name')->all());
        $this->assertEqualsWithDelta(800, $product->variants->firstWhere('variant_name', 'Mini')->effectivePrice(), 0.001);
        $this->assertSame('pre_order', $product->variants->firstWhere('variant_name', 'Maxi')->status);
        $this->assertCount(2, $product->images);
    }

    public function test_the_add_page_ignores_variant_rows_when_balls_pricing_is_chosen(): void
    {
        $balls = Category::firstOrCreate(['name' => 'Mystery Balls']);

        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Balls On Add ' . uniqid(), 'description' => 'balls-add', 'category_id' => $balls->id,
            'pricing_mode' => 'automatic', 'base_price' => 1000, 'max_qty' => 10,
            'new_variants' => [['variant_name' => 'Leftover', 'price' => 5, 'min_qty' => 1, 'max_qty' => 1, 'stock' => 0, 'status' => 'active']],
        ])->assertSessionHasNoErrors();

        $product = Product::with('variants')->where('description', 'balls-add')->latest('id')->firstOrFail();
        $this->assertEqualsCanonicalizing(['Pack of 5', 'Pack of 10'], $product->variants->pluck('variant_name')->all());
    }
}
