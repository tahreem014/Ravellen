<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The admin moves an order through Processing → Shipped → Out for Delivery →
 * Delivered by hand; the customer follows it in their own account only
 * (Order Details, My Orders, Track Order), with no emails. Rolled back.
 */
class ManualOrderTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();

        $this->admin    = User::factory()->create(['is_admin' => true]);
        $this->customer = User::factory()->create();
        $this->order    = $this->orderFor($this->customer, Order::STATUS_PROCESSING);
    }

    private function orderFor(User $user, string $status, string $type = Order::TYPE_NORMAL): Order
    {
        $product = Product::create(['name' => 'Tracking Test ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Gift Boxes'])->id]);
        $variant = $product->variants()->create(['variant_name' => 'Standard', 'price' => 2500, 'min_qty' => 1, 'max_qty' => 5, 'stock' => 5, 'status' => 'active']);

        $order = Order::create([
            'user_id' => $user->id, 'order_type' => $type, 'shipping_name' => $user->name, 'shipping_address' => 'Test street',
            'total_amount' => 2500, 'order_date' => now(), 'order_status' => $status, 'payment_method' => 'credit_card',
            'payment_status' => 'paid', 'tracking_number' => 'RVL-' . strtoupper(uniqid()),
        ]);
        $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => 1, 'price' => 2500]);

        return $order;
    }

    private function adminSets(string $status, array $extra = [])
    {
        return $this->actingAs($this->admin)
            ->putJson(route('admin.orders.update', $this->order), ['order_status' => $status] + $extra)
            ->assertOk()->assertExactJson(['message' => 'Order updated.']);
    }

    /** The step states on the customer's Order Details page, in order. */
    private function stepsOnOrderPage(): array
    {
        $html = $this->actingAs($this->customer)->get(route('orders.show', $this->order))->assertOk()->getContent();
        preg_match_all('#<div class="track-step (\w+)">\s*<span class="track-dot"></span>\s*<div>\s*<div class="track-label">([^<]+)</div>#', $html, $m);

        return array_combine($m[2], $m[1]);
    }

    public function test_admin_moves_the_order_along_and_the_customer_sees_each_step(): void
    {
        $this->assertSame(
            ['Order Placed' => 'done', 'Confirmed' => 'done', 'Processing' => 'active', 'Shipped' => 'pending', 'Out for Delivery' => 'pending', 'Delivered' => 'pending'],
            $this->stepsOnOrderPage()
        );

        $this->adminSets(Order::STATUS_SHIPPED);
        $this->assertSame('active', $this->stepsOnOrderPage()['Shipped']);

        $this->adminSets(Order::STATUS_OUT_FOR_DELIVERY);
        $steps = $this->stepsOnOrderPage();
        $this->assertSame(['done', 'active', 'pending'], [$steps['Shipped'], $steps['Out for Delivery'], $steps['Delivered']]);

        // My Orders and Track Order show the same latest status
        $this->actingAs($this->customer)->get(route('profile'))->assertOk()->assertSee('Out for Delivery');
        $this->actingAs($this->customer)->getJson(route('track-order.lookup', ['tracking_number' => $this->order->tracking_number]))
            ->assertOk()
            ->assertJsonPath('status_label', 'Out for Delivery')
            ->assertJsonPath('timeline.4', ['label' => 'Out for Delivery', 'date' => 'Current status', 'state' => 'active']);

        $this->adminSets(Order::STATUS_DELIVERED);
        $this->assertNotNull($this->order->fresh()->delivered_at);
        $this->assertSame(['done'], array_values(array_unique($this->stepsOnOrderPage())));
        $this->actingAs($this->customer)->get(route('orders.show', $this->order))->assertSee(now()->format('d M Y'));

        // a mistake can be corrected, and the delivered date goes with it
        $this->adminSets(Order::STATUS_OUT_FOR_DELIVERY);
        $this->assertNull($this->order->fresh()->delivered_at);
    }

    public function test_status_updates_reach_the_website_only_never_email(): void
    {
        foreach ([Order::STATUS_SHIPPED, Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_DELIVERED] as $status) {
            $this->adminSets($status);
        }

        Notification::assertSentToTimes($this->customer, OrderStatusUpdatedNotification::class, 3);
        Notification::assertSentTo($this->customer, OrderStatusUpdatedNotification::class,
            fn ($notification, array $channels) => $channels === ['database']);
        Notification::assertSentTo($this->customer, OrderStatusUpdatedNotification::class,
            fn ($notification) => $notification->toDatabase($this->customer)['message'] === 'Your order is out for delivery and will reach you soon.');
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_the_order_reference_stays_ravellens_own(): void
    {
        $reference = $this->order->tracking_number;

        $this->adminSets(Order::STATUS_SHIPPED, ['tracking_number' => 'COURIER-123']);

        $this->assertSame($reference, $this->order->fresh()->tracking_number);
        $this->actingAs($this->admin)->get(route('admin.orders.show', $this->order))
            ->assertOk()
            ->assertSee('<option value="out_for_delivery" >Out for Delivery</option>', false)
            ->assertDontSee('name="tracking_number"', false);
    }

    public function test_customers_only_see_their_own_orders(): void
    {
        $other = User::factory()->create();
        $theirs = $this->orderFor($other, Order::STATUS_SHIPPED);

        $this->actingAs($this->customer)->get(route('orders.show', $theirs))->assertNotFound();
        $this->actingAs($this->customer)->getJson(route('track-order.lookup', ['tracking_number' => $theirs->tracking_number]))->assertNotFound();
        $this->actingAs($this->customer)->get(route('track-order', ['tracking_number' => $theirs->tracking_number]))->assertOk()
            ->assertSee('const initialResult = null;', false)                        // nothing found for them
            ->assertDontSee("fillAndTrack('{$theirs->tracking_number}')", false);     // and not in their list
        $this->actingAs($this->customer)->get(route('profile'))->assertOk()->assertDontSee($theirs->tracking_number);

        // and only the admin can change a status
        $this->actingAs($this->customer)->putJson(route('admin.orders.update', $this->order), ['order_status' => Order::STATUS_DELIVERED]);
        $this->assertSame(Order::STATUS_PROCESSING, $this->order->fresh()->order_status);

        auth()->logout();
        $this->get(route('orders.show', $this->order))->assertRedirect(route('login'));
    }

    public function test_orders_are_tracked_by_tracking_number_only(): void
    {
        $lookup = fn (string $value) => $this->actingAs($this->customer)->getJson(route('track-order.lookup', ['tracking_number' => $value]));

        $lookup($this->order->tracking_number)->assertOk()
            ->assertJsonPath('tracking_number', $this->order->tracking_number)
            ->assertJsonMissingPath('id');
        $lookup((string) $this->order->id)->assertNotFound()
            ->assertJsonPath('message', 'No order found with that tracking number. Please check it and try again.');
        $lookup($this->order->uuid)->assertNotFound();

        // the page lists orders by tracking number, and the profile links there with it
        $this->actingAs($this->customer)->get(route('track-order', ['tracking_number' => $this->order->tracking_number]))->assertOk()
            ->assertSee('<div class="order-id-text">' . $this->order->tracking_number . '</div>', false)
            ->assertDontSee('Order #' . $this->order->id)
            ->assertSee('"tracking_number":"' . $this->order->tracking_number . '"', false);   // preloaded result
        $this->actingAs($this->customer)->get(route('profile'))
            ->assertSee(route('track-order', ['tracking_number' => $this->order->tracking_number]), false);
    }

    public function test_customers_see_the_tracking_number_never_the_order_id(): void
    {
        $id = 'Order #' . $this->order->id;

        $this->actingAs($this->customer)->get(route('profile'))->assertOk()
            ->assertSee($this->order->tracking_number)
            ->assertDontSee($id)->assertDontSee('Order # ' . $this->order->id)
            ->assertDontSee('View Invoice');
        $this->actingAs($this->customer)->get(route('orders.show', $this->order))->assertOk()
            ->assertSee('<title>' . $this->order->tracking_number . ' | Ravellen</title>', false)
            ->assertDontSee($id);

        // the admin still works with order numbers
        $this->actingAs($this->admin)->get(route('admin.orders.show', $this->order))->assertOk()->assertSee($id);
    }

    public function test_unpaid_and_pre_orders_get_the_right_steps(): void
    {
        $unpaid = $this->orderFor($this->customer, Order::STATUS_PENDING);
        $this->assertSame(['done', 'pending'], array_values(array_unique(array_column($unpaid->trackingSteps(), 'state'))));

        $preOrder = $this->orderFor($this->customer, Order::STATUS_OUT_FOR_DELIVERY, Order::TYPE_PRE_ORDER);
        $this->assertSame(
            ['Order Placed', 'Awaiting Stock', 'Payment Due', 'Confirmed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered'],
            array_column($preOrder->trackingSteps(), 'label')
        );
        $this->assertSame('active', $preOrder->trackingSteps()[6]['state']);
    }
}
