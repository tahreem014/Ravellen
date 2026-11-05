<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * An order is saved before its payment starts, so when Safepay's page can't be
 * opened the customer must get a plain, retryable answer — never a stack trace
 * or an internal message. Rolled back.
 */
class PaymentStartFailureTest extends TestCase
{
    use DatabaseTransactions;

    private User $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();
        $this->order    = Order::create([
            'user_id' => $this->customer->id, 'order_type' => Order::TYPE_NORMAL, 'shipping_name' => $this->customer->name,
            'shipping_address' => 'Test street', 'total_amount' => 2500, 'order_date' => now(),
            'order_status' => Order::STATUS_PENDING, 'payment_method' => 'credit_card',
            'payment_status' => Order::PAYMENT_PENDING, 'tracking_number' => 'RVL-' . strtoupper(uniqid()),
        ]);
    }

    private function pay()
    {
        return $this->actingAs($this->customer)->postJson(route('payments.initiate', $this->order));
    }

    public function test_safepay_refusing_the_session_says_try_again_and_keeps_the_order(): void
    {
        Http::fake(['*' => Http::response(['status' => ['message' => 'error']], 500)]);

        $this->pay()
            ->assertStatus(503)
            ->assertJson(['success' => false])
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'try the payment again') && ! str_contains($message, 'Safepay is not configured'));

        $this->assertSame(Order::PAYMENT_PENDING, $this->order->fresh()->payment_status);
    }

    public function test_safepay_being_unreachable_says_try_again_instead_of_a_server_error(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Connection timed out'));

        $this->pay()
            ->assertStatus(503)
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'try the payment again') && ! str_contains($message, 'cURL'));
    }

    public function test_an_order_that_is_not_payable_keeps_its_own_clear_message(): void
    {
        $this->order->update(['payment_status' => Order::PAYMENT_PAID]);

        $this->pay()->assertStatus(422)->assertJsonPath('message', 'This order is not awaiting payment.');
    }

    public function test_too_many_attempts_is_throttled_with_a_wait_time_the_page_can_show(): void
    {
        Http::fake(['*' => Http::response(['status' => ['message' => 'error']], 500)]);

        foreach (range(1, 10) as $attempt) {
            $this->pay()->assertStatus(503);
        }

        $this->pay()->assertStatus(429)->assertHeader('Retry-After');
    }
}
