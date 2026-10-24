<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Several photos and videos uploaded at once from the admin Add / Edit
 * product forms. Database changes are rolled back and every file written to
 * public/images/products is deleted again.
 */
class ProductMediaUploadTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Category $category;

    /** @var string[] files this test wrote to public/images/products */
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

    public function test_edit_page_saves_several_photos_and_videos_at_once(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin)->post(route('admin.products.images.store', $product), [
            'media' => [
                UploadedFile::fake()->image('front.jpg', 600, 400),
                UploadedFile::fake()->image('side.png', 600, 400),
                UploadedFile::fake()->create('unboxing.mp4', 800, 'video/mp4'),
            ],
        ])->assertRedirect(route('admin.products.edit', $product))
          ->assertSessionHas('status', '3 files uploaded.');

        $media = $this->rememberFiles($product);

        $this->assertCount(3, $media);
        $this->assertSame(2, $media->whereNotNull('image')->count());
        $this->assertSame(1, $media->whereNotNull('video')->count());
        $this->assertStringEndsWith('.mp4', $media->whereNotNull('video')->first()->video);
        foreach ($this->writtenFiles as $file) {
            $this->assertFileExists(public_path('images/products/' . $file));
        }
    }

    public function test_new_product_is_created_together_with_its_media(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'Media Test Box',
            'category_id' => $this->category->id,
            'media' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.webp'),
                UploadedFile::fake()->create('clip.mov', 500, 'video/quicktime'),
            ],
        ])->assertSessionHasNoErrors()->assertSessionHas('status', 'Product created with 3 photos/videos. Now add its variants below.');

        $product = Product::where('name', 'Media Test Box')->latest('id')->firstOrFail();

        $this->assertCount(3, $this->rememberFiles($product));
    }

    public function test_media_is_optional_when_creating_a_product(): void
    {
        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name' => 'No Media Box',
            'category_id' => $this->category->id,
        ])->assertSessionHasNoErrors();

        $this->assertCount(0, Product::where('name', 'No Media Box')->firstOrFail()->images);
    }

    public function test_invalid_files_are_rejected_and_nothing_is_saved(): void
    {
        $product = $this->product();
        $upload = fn (array $media) => $this->actingAs($this->admin)
            ->post(route('admin.products.images.store', $product), ['media' => $media]);

        $upload([UploadedFile::fake()->image('ok.jpg'), UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors(['media.1' => 'Only JPG, PNG or WEBP photos and MP4 or MOV videos can be uploaded.']);

        $upload([UploadedFile::fake()->create('huge.jpg', 3000, 'image/jpeg')])
            ->assertSessionHasErrors('media.0');

        $this->actingAs($this->admin)->post(route('admin.products.images.store', $product), [])
            ->assertSessionHasErrors(['media' => 'Choose at least one photo or video.']);

        $this->assertCount(0, $product->fresh()->images);
    }

    public function test_add_and_edit_forms_offer_the_multi_file_picker(): void
    {
        $this->actingAs($this->admin)->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="media[]"', false)
            ->assertSee('Choose photos &amp; videos', false);

        $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product()))
            ->assertOk()
            ->assertSee('name="media[]" id="mediaInput" class="d-none" multiple', false)
            ->assertSee('Save media');
    }

    private function product(): Product
    {
        return Product::create(['name' => 'Media Test ' . uniqid(), 'category_id' => $this->category->id]);
    }

    private function rememberFiles(Product $product)
    {
        $media = $product->fresh()->images;
        $this->writtenFiles = array_merge($this->writtenFiles, $media->pluck('image')->merge($media->pluck('video'))->filter()->all());

        return $media;
    }
}
