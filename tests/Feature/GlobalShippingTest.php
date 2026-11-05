<?php

namespace Tests\Feature;

use App\Models\AddToCart;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\User;
use App\Services\LocationService;
use App\Services\ShippingRateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Shipping for the whole world: countries and cities come from the bundled location list, the
 * admin sets a default rate per country and optional rates per city, and a customer is charged
 * the city's rate, else the country's, else cannot check out. Rolled back.
 *
 * Germany and Japan are used because the shop's real rates only cover Pakistan.
 */
class GlobalShippingTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // These tests run on the shop's own database, where real rates may exist for any country.
        // Clear the ones for the countries used here; the rollback at the end puts them back.
        ShippingRate::whereIn('country', ['Germany', 'Japan', 'Switzerland'])->delete();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // ── location data ───────────────────────────────────────────────────────

    public function test_every_country_and_its_cities_come_from_the_bundled_list(): void
    {
        $locations = app(LocationService::class);

        $this->assertGreaterThan(200, count($locations->countries()));
        $this->assertSame('PK', $locations->findCountry('pakistan')['code']);
        $this->assertSame('Pakistan', $locations->findCountry(' PK ')['name']);
        $this->assertNull($locations->findCountry('Atlantis'));

        $this->assertContains('Karachi', $locations->cities('PK'));
        $this->assertContains('Berlin', $locations->cities('de'));
        $this->assertSame([], $locations->cities('ZZ'));

        $this->assertSame('Pakistan', $locations->canonicalCountryName('pakistan'));
        $this->assertSame('Karachi', $locations->canonicalCityName('pakistan', ' karachi '));
        $this->assertSame('Tiny Hamlet', $locations->canonicalCityName('Germany', 'Tiny  Hamlet'), 'a city the list lacks is kept as typed');
    }

    public function test_cities_load_for_a_chosen_country_without_signing_in(): void
    {
        $this->getJson(route('locations.cities', 'pk'))
            ->assertOk()
            ->assertJsonPath('country', 'Pakistan')
            ->assertJsonFragment(['Karachi'])
            ->assertHeader('Cache-Control', 'max-age=86400, public');

        $this->getJson('/locations/ZZ/cities')->assertNotFound();
        $this->getJson('/locations/pakistan/cities')->assertNotFound();
    }

    // ── which rate applies ──────────────────────────────────────────────────

    public function test_a_city_rate_wins_and_every_other_city_gets_the_country_default(): void
    {
        $this->rate('Germany', null, 900);
        $this->rate('Germany', 'Berlin', 400);
        $rates = app(ShippingRateService::class);

        $this->assertSame(400.0, (float) $rates->findActiveRate('Germany', 'Berlin')->amount);
        $this->assertSame(400.0, (float) $rates->findActiveRate('germany', '  berlin ')->amount, 'case and spacing do not matter');
        $this->assertSame(400.0, (float) $rates->findActiveRate('DE', 'Berlin')->amount, 'nor does naming the country by its code');
        $this->assertSame(900.0, (float) $rates->findActiveRate('Germany', 'Munich')->amount);
        $this->assertSame(900.0, (float) $rates->findActiveRate('Germany', null)->amount);
    }

    public function test_no_rate_means_no_delivery(): void
    {
        $this->rate('Germany', 'Berlin', 400);   // a city rate alone does not make the country deliverable
        $rates = app(ShippingRateService::class);

        $this->assertNull($rates->findActiveRate('Germany', 'Munich'));
        $this->assertNull($rates->findActiveRate('Japan', 'Tokyo'));
        $this->assertNull($rates->findActiveRate('', 'Tokyo'));
        $this->assertNull($rates->findActiveRate(null, null));
        $this->assertSame('Delivery is not available to Tokyo, Japan yet.', $rates->unavailableMessage('japan', 'Tokyo'));
    }

    public function test_switched_off_rates_are_ignored(): void
    {
        $this->rate('Germany', null, 900);
        $this->rate('Germany', 'Berlin', 400, active: false);
        $this->rate('Japan', null, 1500, active: false);
        $rates = app(ShippingRateService::class);

        $this->assertSame(900.0, (float) $rates->findActiveRate('Germany', 'Berlin')->amount, 'falls back to the country default');
        $this->assertNull($rates->findActiveRate('Japan', 'Tokyo'));
    }

    public function test_accents_do_not_stop_a_city_matching(): void
    {
        $this->rate('Switzerland', null, 1200);
        $this->rate('Switzerland', 'Zürich', 700);

        $this->assertSame(700.0, (float) app(ShippingRateService::class)->findActiveRate('Switzerland', 'zurich')->amount);
    }

    public function test_rates_typed_in_by_hand_before_the_pickers_still_match(): void
    {
        ShippingRate::create(['country' => 'pAkIsTaN', 'city' => 'lahore ', 'amount' => 300, 'is_active' => true]);

        $this->assertSame(300.0, (float) app(ShippingRateService::class)->findActiveRate('Pakistan', 'Lahore')->amount);
    }

    // ── quoting at checkout ─────────────────────────────────────────────────

    public function test_guests_can_be_quoted_from_the_country_and_city_they_pick(): void
    {
        $this->rate('Germany', null, 900);
        $this->rate('Germany', 'Berlin', 400);

        $this->getJson(route('shipping.quote', ['country' => 'Germany', 'city' => 'Berlin']))
            ->assertOk()->assertExactJson(['available' => true, 'shipping_fee' => 400.0, 'scope' => 'city']);

        $this->getJson(route('shipping.quote', ['country' => 'Germany', 'city' => 'Munich']))
            ->assertOk()->assertExactJson(['available' => true, 'shipping_fee' => 900.0, 'scope' => 'country']);

        $this->getJson(route('shipping.quote', ['country' => 'Japan', 'city' => 'Tokyo']))
            ->assertStatus(422)
            ->assertExactJson(['available' => false, 'message' => 'Delivery is not available to Tokyo, Japan yet.']);

        $this->getJson(route('shipping.quote', ['city' => 'Berlin']))->assertStatus(422)->assertJsonValidationErrors('country');
    }

    public function test_a_signed_in_customer_is_quoted_from_a_saved_address(): void
    {
        $this->rate('Germany', null, 900);
        $buyer = User::factory()->create();
        $berlin = $this->address($buyer, 'Germany', 'Berlin');
        $tokyo  = $this->address($buyer, 'Japan', 'Tokyo');

        $this->actingAs($buyer)->getJson(route('checkout.shipping-fee', ['address_id' => $berlin->id]))
            ->assertOk()->assertJson(['available' => true, 'shipping_fee' => 900.0, 'scope' => 'country']);
        $this->actingAs($buyer)->getJson(route('checkout.shipping-fee', ['address_id' => $tokyo->id]))
            ->assertStatus(422)->assertJson(['available' => false]);
    }

    public function test_orders_are_charged_by_priority_and_refused_where_we_do_not_deliver(): void
    {
        $default    = $this->rate('Germany', null, 900);
        $berlinRate = $this->rate('Germany', 'Berlin', 400);
        $buyer = User::factory()->create();

        foreach ([['Berlin', 400.0, $berlinRate->id], ['Munich', 900.0, $default->id]] as [$city, $fee, $rateId]) {
            $response = $this->placeOrder($buyer, $this->address($buyer, 'Germany', $city))->assertCreated();
            $order    = Order::where('uuid', $response->json('orders.0.uuid'))->firstOrFail();

            $this->assertSame($fee, (float) $order->shipping_fee, "{$city} is charged {$fee}");
            $this->assertEqualsWithDelta(1000 + $fee, (float) $order->total_amount, 0.001);
            $this->assertSame($rateId, $order->shipping_rate_id, "{$city} is recorded against the rate that priced it");
        }

        $before = Order::where('user_id', $buyer->id)->count();
        $this->placeOrder($buyer, $this->address($buyer, 'Japan', 'Tokyo'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Delivery is not available to Tokyo, Japan yet.');
        $this->assertSame($before, Order::where('user_id', $buyer->id)->count());
    }

    public function test_a_guest_is_not_sent_to_sign_up_for_an_order_we_cannot_deliver(): void
    {
        $this->rate('Germany', null, 900);

        $this->postJson(route('checkout.resume'), $this->guestDetails(['country' => 'Japan', 'city' => 'Tokyo']))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Delivery is not available to Tokyo, Japan yet.');
        $this->assertNull(session('checkout.resume'));

        $this->postJson(route('checkout.resume'), $this->guestDetails(['country' => 'Atlantis', 'city' => 'Poseidonis']))
            ->assertStatus(422)->assertJsonValidationErrors('country');

        $this->postJson(route('checkout.resume'), $this->guestDetails(['country' => 'germany', 'city' => ' Munich ']))
            ->assertOk()
            ->assertSessionHas('checkout.resume.country', 'Germany')
            ->assertSessionHas('checkout.resume.city', 'Munich');
    }

    public function test_guest_checkout_offers_every_country_and_loads_cities_for_the_chosen_one(): void
    {
        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('<select class="form-control-custom" id="country">', false)
            ->assertSee('data-code="PK"', false)
            ->assertSee('data-code="DE"', false)
            ->assertSee('<select class="form-control-custom" id="city">', false)   // a dropdown, filled once a country is chosen
            ->assertSee('id="city-other"', false)                                     // for a city the list doesn't have
            ->assertSee(route('shipping.quote'), false);
    }

    // ── saved addresses ─────────────────────────────────────────────────────

    public function test_addresses_are_saved_with_a_real_country_spelled_the_standard_way(): void
    {
        $user = User::factory()->create();
        $fields = ['name' => 'Ayesha', 'address' => 'House 1', 'phone' => '+923001234567', 'country' => 'pakistan', 'city' => '  Karachi '];

        $this->actingAs($user)->postJson(route('addresses.store'), $fields)->assertOk();
        $address = $user->addresses()->sole();
        $this->assertSame(['Pakistan', 'Karachi'], [$address->country, $address->city]);

        $this->actingAs($user)->putJson(route('addresses.update', $address), ['country' => 'germany', 'city' => 'Berlin'])->assertOk();
        $this->assertSame('Germany', $address->fresh()->country);

        $this->actingAs($user)->postJson(route('addresses.store'), ['country' => 'Narnia'] + $fields)
            ->assertStatus(422)->assertJsonValidationErrors('country');
        $this->assertSame(1, $user->addresses()->count());
    }

    // ── admin management ────────────────────────────────────────────────────

    public function test_only_admins_manage_rates(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.shipping-rates.index'))->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('admin.shipping-rates.store'), ['country' => 'Germany', 'amount' => 1])->assertForbidden();
    }

    public function test_admin_adds_a_country_default_and_city_rates_without_listing_any_cities(): void
    {
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'germany', 'city' => '', 'amount' => '900', 'is_active' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'Shipping rate added.');
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'Germany', 'city' => 'berlin', 'amount' => '400.50', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'Germany', 'city' => 'Tiny Hamlet', 'amount' => '1000', 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            [['Germany', null, '900.00'], ['Germany', 'Berlin', '400.50'], ['Germany', 'Tiny Hamlet', '1000.00']],
            ShippingRate::where('country', 'Germany')->orderBy('id')->get()->map(fn ($r) => [$r->country, $r->city, (string) $r->amount])->all(),
        );
        $this->assertSame(1, ShippingRate::where('country', 'Germany')->whereNull('city')->count());
    }

    public function test_admin_cannot_add_the_same_place_twice_or_an_unknown_country_or_a_bad_charge(): void
    {
        $this->rate('Germany', null, 900);
        $this->rate('Germany', 'Berlin', 400);
        $count = ShippingRate::count();

        foreach ([
            ['country' => 'GERMANY', 'city' => null, 'amount' => 5],     // second default for the country
            ['country' => 'Germany', 'city' => 'BERLIN', 'amount' => 5], // second rate for the city
            ['country' => 'Atlantis', 'city' => null, 'amount' => 5],
            ['country' => 'Japan', 'city' => null, 'amount' => -5],
            ['country' => 'Japan', 'city' => null, 'amount' => 'free'],
            ['country' => 'Japan', 'city' => null, 'amount' => 1000000000],
            ['country' => '', 'city' => null, 'amount' => 5],
        ] as $bad) {
            $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), $bad + ['is_active' => '1'])->assertSessionHasErrors();
        }

        $this->assertSame($count, ShippingRate::count());
    }

    public function test_admin_edits_switches_off_and_deletes_a_rate(): void
    {
        $default = $this->rate('Germany', null, 900);
        $berlin  = $this->rate('Germany', 'Berlin', 400);

        $this->actingAs($this->admin)->put(route('admin.shipping-rates.update', $berlin), ['country' => 'Germany', 'city' => 'Berlin', 'amount' => '450', 'is_active' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'Shipping rate updated.');
        $this->assertSame('450.00', (string) $berlin->fresh()->amount);

        // saving a rate over itself is not a duplicate, but turning it into another existing place is
        $this->actingAs($this->admin)->put(route('admin.shipping-rates.update', $berlin), ['country' => 'Germany', 'city' => '', 'amount' => '450', 'is_active' => '1'])
            ->assertSessionHasErrors('country');
        $this->assertSame('Berlin', $berlin->fresh()->city);

        $this->actingAs($this->admin)->post(route('admin.shipping-rates.toggle-active', $berlin))->assertSessionHas('status', 'Shipping rate deactivated.');
        $this->assertSame(900.0, (float) app(ShippingRateService::class)->findActiveRate('Germany', 'Berlin')->amount);

        $this->actingAs($this->admin)->delete(route('admin.shipping-rates.destroy', $default))->assertSessionHas('status', 'Shipping rate deleted.');
        $this->assertNull(app(ShippingRateService::class)->findActiveRate('Germany', 'Munich'), 'with the default gone, the country is no longer deliverable');
    }

    public function test_admin_page_offers_the_country_list_and_finds_rates_by_search(): void
    {
        $this->rate('Germany', 'Berlin', 400);
        $this->rate('Japan', null, 1500);

        $this->actingAs($this->admin)->get(route('admin.shipping-rates.index'))
            ->assertOk()
            ->assertSee('name="country"', false)
            ->assertSee('data-code="PK"', false)
            ->assertSee('<select name="city" id="shippingRateCity"', false)   // the city dropdown
            ->assertSee('Country default')
            ->assertSee('Berlin');

        $this->actingAs($this->admin)->get(route('admin.shipping-rates.index', ['q' => 'berl']))
            ->assertOk()->assertSee('Berlin')->assertDontSee('Japan</td>', false);
    }

    // ── free shipping ───────────────────────────────────────────────────────

    public function test_admin_sets_free_shipping_without_typing_a_charge(): void
    {
        // the form leaves the amount out when "Free shipping" is ticked; whatever is sent alongside it is ignored
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'Germany', 'city' => '', 'free_shipping' => '1', 'is_active' => '1'])
            ->assertSessionHasNoErrors()->assertSessionHas('status', 'Shipping rate added.');
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'Germany', 'city' => 'Berlin', 'amount' => '400', 'free_shipping' => '1', 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $default = ShippingRate::where('country', 'Germany')->whereNull('city')->firstOrFail();
        $berlin  = ShippingRate::where('country', 'Germany')->where('city', 'Berlin')->firstOrFail();
        $this->assertSame('0.00', (string) $default->amount);
        $this->assertSame('0.00', (string) $berlin->amount);
        $this->assertTrue($default->isFree());

        // a charge is still required when shipping is not free
        $this->actingAs($this->admin)->post(route('admin.shipping-rates.store'), ['country' => 'Japan', 'city' => '', 'is_active' => '1'])
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, ShippingRate::where('country', 'Japan')->count());

        // and a free rate can be turned back into a paid one
        $this->actingAs($this->admin)->put(route('admin.shipping-rates.update', $berlin), ['country' => 'Germany', 'city' => 'Berlin', 'amount' => '450', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame('450.00', (string) $berlin->fresh()->amount);
        $this->assertFalse($berlin->fresh()->isFree());
    }

    public function test_admin_list_shows_free_rates_as_a_green_badge_not_an_amount(): void
    {
        $this->rate('Germany', null, 0);
        $this->rate('Japan', null, 1500);

        $html = $this->actingAs($this->admin)->get(route('admin.shipping-rates.index'))->assertOk()->getContent();

        $this->assertStringContainsString('<span class="badge bg-success">Free shipping</span>', $html);
        $this->assertStringContainsString('PKR 1,500.00', $html);
        $this->assertStringNotContainsString('PKR 0.00', $html);
    }

    public function test_a_free_rate_quotes_and_orders_at_no_charge(): void
    {
        $this->rate('Germany', null, 0);
        $buyer = User::factory()->create();

        $this->getJson(route('shipping.quote', ['country' => 'Germany', 'city' => 'Berlin']))
            ->assertOk()->assertExactJson(['available' => true, 'shipping_fee' => 0.0, 'scope' => 'country']);

        $response = $this->placeOrder($buyer, $this->address($buyer, 'Germany', 'Berlin'))->assertCreated();
        $order    = Order::where('uuid', $response->json('orders.0.uuid'))->firstOrFail();

        $this->assertTrue($order->hasFreeShipping());
        $this->assertEqualsWithDelta(1000, (float) $order->total_amount, 0.001, 'nothing is added to the items');
    }

    public function test_the_order_page_shows_free_shipping_as_a_green_badge_instead_of_an_amount(): void
    {
        $this->rate('Germany', null, 0);
        $this->rate('Japan', null, 1500);
        $buyer = User::factory()->create();

        $free = Order::where('uuid', $this->placeOrder($buyer, $this->address($buyer, 'Germany', 'Berlin'))->json('orders.0.uuid'))->firstOrFail();
        $paid = Order::where('uuid', $this->placeOrder($buyer, $this->address($buyer, 'Japan', 'Tokyo'))->json('orders.0.uuid'))->firstOrFail();

        $this->actingAs($buyer)->get(route('orders.show', $free))
            ->assertOk()->assertSee('<span class="free-ship-badge">Free shipping</span>', false)->assertDontSee('PKR 0');

        $this->actingAs($buyer)->get(route('orders.show', $paid))
            ->assertOk()->assertSee('PKR 1,500')->assertDontSee('<span class="free-ship-badge">', false);
    }

    // ── helpers ─────────────────────────────────────────────────────────────

    private function rate(string $country, ?string $city, float $amount, bool $active = true): ShippingRate
    {
        return ShippingRate::create(['country' => $country, 'city' => $city, 'amount' => $amount, 'is_active' => $active]);
    }

    private function address(User $user, string $country, string $city): Address
    {
        return Address::create([
            'user_id' => $user->id, 'name' => 'Global Test', 'address' => 'Test street',
            'country' => $country, 'city' => $city, 'phone' => '+923001234567', 'is_default' => false,
        ]);
    }

    private function guestDetails(array $override = []): array
    {
        return $override + [
            'name' => 'Guest Buyer', 'phone' => '+923001234567', 'address' => 'House 12',
            'city' => 'Berlin', 'country' => 'Germany', 'payment_method' => 'easypaisa',
        ];
    }

    /** A cart of one PKR 1,000 item, ordered to the given address. */
    private function placeOrder(User $buyer, Address $address)
    {
        $product = Product::create(['name' => 'Global Shipping Mug ' . uniqid(), 'category_id' => Category::firstOrCreate(['name' => 'Mystery Balls'])->id]);
        $variant = $product->variants()->create([
            'variant_name' => 'Mug', 'price' => 1000, 'min_qty' => 1, 'max_qty' => 10, 'stock' => 5, 'status' => 'active',
        ]);
        $line = AddToCart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'qty' => 1]);

        return $this->actingAs($buyer)->postJson(route('orders.store'), [
            'shipping_name' => 'Global Test', 'shipping_phone' => '+923001234567', 'shipping_address' => 'Test street',
            'payment_method' => 'credit_card', 'address_id' => $address->id, 'cart_item_ids' => [$line->id],
        ]);
    }
}
