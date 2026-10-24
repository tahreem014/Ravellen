<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Admin forms marked data-ajax are saved in the background: their usual
 * redirect-with-message comes back as JSON (RespondToBackgroundForms), errors
 * as 422 JSON, and the page re-renders its sections in place. Rolled back.
 */
class AdminInPlaceSavingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->product = Product::create(['name' => 'In Place Test ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);
        $this->product->variants()->create(['variant_name' => 'Standard', 'price' => 4000, 'min_qty' => 1, 'max_qty' => 5, 'stock' => 5, 'status' => 'active']);
        $this->product->load('variants');
    }

    public function test_variant_edits_answer_with_their_message_instead_of_a_redirect(): void
    {
        $variant = $this->product->variants->first();
        $fields = ['variant_name' => 'Standard', 'price' => 4000, 'min_qty' => 1, 'max_qty' => 5, 'stock' => 5, 'status' => 'active'];

        $this->actingAs($this->admin)
            ->putJson(route('admin.products.variants.update', [$this->product, $variant]), $fields + ['sale_price' => 3000])
            ->assertOk()
            ->assertExactJson(['message' => 'Variant updated.'])
            ->assertSessionMissing('status');   // no stray toast on the next page load
        $this->assertSame(3000.0, $variant->fresh()->effectivePrice());

        $this->actingAs($this->admin)
            ->putJson(route('admin.products.variants.update', [$this->product, $variant]), $fields + ['sale_price' => 5000])
            ->assertStatus(422)
            ->assertJsonPath('errors.sale_price.0', 'The sale price must be lower than the regular price.');

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.variants.store', $this->product), ['variant_name' => 'Large', 'price' => 6000] + $fields)
            ->assertOk()->assertExactJson(['message' => 'Variant added.']);

        $large = $this->product->variants()->where('variant_name', 'Large')->firstOrFail();
        $this->actingAs($this->admin)
            ->deleteJson(route('admin.products.variants.destroy', [$this->product, $large]))
            ->assertOk()->assertExactJson(['message' => 'Variant removed.']);
    }

    public function test_redirects_with_errors_become_a_422_with_the_message(): void
    {
        $this->product->update(['pricing_mode' => Product::PRICING_CUSTOM]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.products.variants.store', $this->product), ['variant_name' => 'Pack of 5'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Add or remove quantities in the Pricing section above.');
    }

    public function test_normal_form_submits_still_redirect_as_before(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.products.sale', $this->product), ['percent' => 10])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_media_uploads_and_removals_save_in_place(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.products.images.store', $this->product), ['media' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')]])
            ->assertOk()->assertExactJson(['message' => '2 files uploaded.']);

        $images = $this->product->images()->get();
        $this->assertCount(2, $images);

        foreach ($images as $image) {
            $this->actingAs($this->admin)
                ->deleteJson(route('admin.products.images.destroy', [$this->product, $image]))
                ->assertOk()->assertExactJson(['message' => 'Media removed.']);
            $this->assertFileDoesNotExist(public_path('images/products/' . $image->image));
        }
    }

    public function test_order_status_and_refund_decisions_save_in_place(): void
    {
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id, 'order_type' => 'normal', 'shipping_name' => 'In Place', 'shipping_address' => 'Test street',
            'total_amount' => 4000, 'order_date' => now(), 'order_status' => 'confirmed', 'payment_method' => 'credit_card',
            'payment_status' => 'paid', 'tracking_number' => 'RVL-INPLACE',
        ]);
        $item = $order->items()->create(['product_id' => $this->product->id, 'product_variant_id' => $this->product->variants->first()->id, 'qty' => 1, 'price' => 4000]);

        $this->actingAs($this->admin)
            ->putJson(route('admin.orders.update', $order), ['order_status' => 'shipped', 'tracking_number' => 'RVL-INPLACE'])
            ->assertOk()->assertExactJson(['message' => 'Order updated.']);
        $this->assertSame('shipped', $order->fresh()->order_status);

        $refund = RefundRequest::create(['order_item_id' => $item->id, 'amount' => 4000, 'bank_details' => 'Bank', 'reason' => 'Test', 'requested_at' => now()]);

        $this->actingAs($this->admin)
            ->putJson(route('admin.refund-requests.update', $refund), ['status' => 'approved'])
            ->assertOk()->assertExactJson(['message' => 'Refund request approved.']);

        $this->actingAs($this->admin)
            ->putJson(route('admin.refund-requests.update', $refund), ['status' => 'rejected'])
            ->assertStatus(422)->assertJsonPath('message', 'This refund request has already been processed.');
    }

    public function test_admin_pages_mark_their_in_place_forms_and_sections(): void
    {
        $edit = $this->actingAs($this->admin)->get(route('admin.products.edit', $this->product))->assertOk();
        foreach (['discount', 'pricing', 'variants', 'media'] as $region) {
            $edit->assertSee('data-admin-region="' . $region . '"', false);
        }
        $edit->assertSee('enctype="multipart/form-data" data-ajax', false);

        $this->actingAs($this->admin)->get(route('admin.refund-requests.index'))->assertOk()->assertSee('data-admin-region="refunds"', false);
    }
}
