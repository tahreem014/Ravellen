<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * A guest who presses "Place Order" logs in or registers first, then carries on to payment
 * with the details they had entered (kept in their session for 30 minutes). Rolled back.
 */
class GuestCheckoutResumeTest extends TestCase
{
    use DatabaseTransactions;

    private function details(array $override = []): array
    {
        return $override + [
            'name'           => 'Guest Buyer',
            'phone'          => '+923001234567',
            'address'        => 'House 12, Street 4, DHA',
            'city'           => 'Karachi',
            'country'        => 'Pakistan',
            'payment_method' => 'easypaisa',
        ];
    }

    /** A guest's session as it is after pressing Place Order. */
    private function pendingSession(int $minutesAgo = 0): array
    {
        return ['checkout.resume' => $this->details() + ['saved_at' => now()->subMinutes($minutesAgo)->timestamp]];
    }

    public function test_pressing_place_order_as_a_guest_saves_the_details_and_points_at_login(): void
    {
        $this->postJson(route('checkout.resume'), $this->details())
            ->assertOk()
            ->assertJson(['redirect' => route('login')])
            ->assertSessionHas('checkout.resume.city', 'Karachi')
            ->assertSessionHas('checkout.resume.payment_method', 'easypaisa');
    }

    public function test_incomplete_or_malformed_details_are_refused_and_nothing_is_kept(): void
    {
        foreach ([
            ['phone' => '0300-1234567'],        // no country code
            ['payment_method' => 'cash'],       // not an online method
            ['city' => ''],
            ['name' => ''],
        ] as $bad) {
            $this->postJson(route('checkout.resume'), $this->details($bad))->assertStatus(422);
        }

        $this->assertNull(session('checkout.resume'));
    }

    public function test_login_and_register_pages_explain_the_extra_step_only_while_details_are_waiting(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('One last step');
        $this->get('/register')->assertOk()->assertDontSee('One last step');

        $this->withSession($this->pendingSession())->get('/login')->assertOk()->assertSee('One last step')->assertSee('saved for 30 minutes');
        $this->withSession($this->pendingSession())->get('/register')->assertOk()->assertSee('One last step');
    }

    public function test_logging_in_with_details_waiting_goes_to_checkout_not_home(): void
    {
        $user = User::factory()->create();

        $this->withSession($this->pendingSession())
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('checkout'));
    }

    public function test_registering_with_details_waiting_goes_to_checkout_and_is_signed_in(): void
    {
        $this->withSession($this->pendingSession())
            ->post('/register', [
                'name' => 'New Buyer', 'email' => 'new-buyer-' . uniqid() . '@example.com',
                'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'phone_number' => '+923001112233',
            ])
            ->assertRedirect(route('checkout'));

        $this->assertAuthenticated();
    }

    public function test_logging_in_normally_still_goes_home_and_admins_still_reach_their_dashboard(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/');
        $this->post('/logout');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->withSession($this->pendingSession())
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_details_that_have_expired_are_ignored(): void
    {
        $user = User::factory()->create();

        $this->withSession($this->pendingSession(minutesAgo: 31))
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/');

        $this->post('/logout');
        $this->withSession($this->pendingSession(minutesAgo: 31))->get('/login')->assertDontSee('One last step');
    }

    public function test_checkout_after_sign_in_saves_the_details_as_an_address_and_continues_once(): void
    {
        $user = User::factory()->create();

        $page = $this->actingAs($user)->withSession($this->pendingSession())->get(route('checkout'))->assertOk();

        $page->assertSee('const RESUME = {"payment_method":"easypaisa"}', false)
            ->assertSee('id="resume-banner"', false)
            ->assertSee('House 12, Street 4, DHA')
            ->assertSee('value="+923001234567"', false);

        $address = $user->addresses()->sole();
        $this->assertSame(['Guest Buyer', 'Karachi', 'Pakistan', true], [$address->name, $address->city, $address->country, $address->is_default]);
        $this->assertNull(session('checkout.resume'), 'the waiting details are used up');
        $this->assertSelectedAddress($page, $address->id);

        // Opening checkout again neither repeats the hand-over nor adds a second address
        $again = $this->actingAs($user)->get(route('checkout'))->assertOk();
        $again->assertSee('const RESUME = null', false)->assertDontSee('id="resume-banner"', false);
        $this->assertSame(1, $user->addresses()->count());
    }

    public function test_a_matching_saved_address_is_reused_not_duplicated(): void
    {
        $user = User::factory()->create();
        $existing = $user->addresses()->create($this->details() + ['is_default' => true]);

        $page = $this->actingAs($user)->withSession($this->pendingSession())->get(route('checkout'))->assertOk();

        $this->assertSame(1, $user->addresses()->count());
        $this->assertSelectedAddress($page, $existing->id);
    }

    public function test_the_resumed_address_is_chosen_even_when_the_account_has_others(): void
    {
        $user = User::factory()->create();
        $user->addresses()->create(['name' => 'Old', 'phone' => '+923009999999', 'address' => 'Elsewhere', 'city' => 'Lahore', 'country' => 'Pakistan', 'is_default' => true]);

        $page = $this->actingAs($user)->withSession($this->pendingSession())->get(route('checkout'))->assertOk();

        $new = $user->addresses()->where('city', 'Karachi')->sole();
        $this->assertFalse($new->is_default, 'an account that already has a default keeps it');
        $this->assertSelectedAddress($page, $new->id);
    }

    public function test_guests_see_city_and_country_and_no_longer_get_a_pretend_order(): void
    {
        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('id="city"', false)
            ->assertSee('id="country"', false)
            ->assertSee('const RESUME = null', false)
            ->assertSee(route('checkout.resume'), false)
            ->assertDontSee('rv_orders', false);   // the old local-only "order" that was never paid for
    }

    private function assertSelectedAddress($response, int $addressId): void
    {
        $this->assertMatchesRegularExpression('#<option value="' . $addressId . '"[^>]*\sselected#s', $response->getContent());
    }
}
