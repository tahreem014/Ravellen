<?php

namespace Tests\Feature;

use App\Http\Middleware\RecordVisit;
use App\Models\Order;
use App\Models\SiteEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The admin dashboard's Website Visits / Total Clicks, counted from real
 * storefront traffic. Rolled back after each test.
 */
class SiteAnalyticsTest extends TestCase
{
    use DatabaseTransactions;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('User-Agent', self::BROWSER);
    }

    public function test_a_visit_starts_with_the_first_page_and_later_pages_of_the_same_visit_do_not_add_one(): void
    {
        $before = SiteEvent::visits()->count();

        $first = $this->get('/about')->assertOk();
        $cookie = $first->getCookie(RecordVisit::COOKIE);

        $this->assertNotNull($cookie, 'the visit id is handed to the browser');
        $this->assertSame($before + 1, SiteEvent::visits()->count());
        $this->assertDatabaseHas('site_events', ['type' => SiteEvent::VISIT, 'visit_id' => $cookie->getValue()]);

        $this->get('/about')->assertOk();
        $this->assertSame($before + 2, SiteEvent::visits()->count(), 'a browser without the cookie is a new visit');

        // withCookie() sticks to every later request of the test, so this goes last
        $this->withCookie(RecordVisit::COOKIE, $cookie->getValue())->get('/contact')->assertOk();
        $this->assertSame($before + 2, SiteEvent::visits()->count(), 'same visit, still one');
    }

    public function test_crawlers_admins_admin_pages_and_background_requests_are_not_visits(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $before = SiteEvent::count();

        $bot = $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')->get('/about')->assertOk();
        $this->assertNull($bot->getCookie(RecordVisit::COOKIE));

        $this->withHeader('User-Agent', self::BROWSER);
        $this->actingAs($admin)->get('/about')->assertOk();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->withHeader('X-Live-Refresh', '1')->get('/about')->assertOk();
        $this->withHeader('X-Live-Refresh', '');
        $this->getJson('/live')->assertOk();
        $this->get('/no-such-page')->assertNotFound();

        $this->assertSame($before, SiteEvent::count());
    }

    public function test_clicks_are_counted_only_inside_a_recorded_visit_and_leave_the_session_alone(): void
    {
        $visitId = $this->get('/about')->getCookie(RecordVisit::COOKIE)->getValue();
        $before = SiteEvent::clicks()->count();

        $this->post(route('track.click'))->assertNoContent();
        $this->assertSame($before, SiteEvent::clicks()->count(), 'no visit cookie, no click');

        $this->withCookie(RecordVisit::COOKIE, 'not-a-visit-id')->post(route('track.click'))->assertNoContent();
        $this->assertSame($before, SiteEvent::clicks()->count(), 'a made-up visit id is ignored');

        $response = $this->withCookie(RecordVisit::COOKIE, $visitId)->post(route('track.click'))->assertNoContent();
        $this->assertSame($before + 1, SiteEvent::clicks()->count());
        $this->assertDatabaseHas('site_events', ['type' => SiteEvent::CLICK, 'visit_id' => $visitId]);
        $this->assertSame([], $response->headers->getCookies(), 'a background beacon must not overwrite the shopper\'s session cookie');
    }

    public function test_storefront_pages_carry_the_click_tracker(): void
    {
        $this->get('/about')->assertOk()->assertSee(route('track.click', [], false));
        $this->get('/login')->assertOk()->assertSee(route('track.click', [], false));
    }

    public function test_dashboard_shows_the_real_counts_and_series(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $visits = SiteEvent::visits()->count();
        $clicks = SiteEvent::clicks()->count();
        $today = SiteEvent::visits()->within(Carbon::today(), Carbon::today()->endOfDay())->count();
        $twoDaysAgo = SiteEvent::visits()->within(Carbon::today()->subDays(2), Carbon::today()->subDays(2)->endOfDay())->count();

        $this->record(SiteEvent::VISIT, now());
        $this->record(SiteEvent::VISIT, now());
        $this->record(SiteEvent::VISIT, now()->subDays(2));
        $this->record(SiteEvent::CLICK, now());

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertSame($visits + 3, $response->viewData('websiteVisits'));
        $this->assertSame($clicks + 1, $response->viewData('totalClicks'));

        $daily = $response->viewData('chartData')['daily']['visits'];
        $this->assertCount(7, $daily);
        $this->assertSame($today + 2, $daily[6], 'today is the last daily slot');
        $this->assertSame($twoDaysAgo + 1, $daily[4]);
        $this->assertSame($visits + 3, SiteEvent::visits()->count(), 'the dashboard visit itself is not counted');
    }

    public function test_the_overview_polls_a_stats_endpoint_that_follows_visits_clicks_and_orders(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('const STATS_URL = ' . json_encode(route('admin.overview.stats', [], false)), false)
            ->assertSee('id="stat-visits"', false)
            ->assertSee('id="stat-revenue"', false);

        $before = $this->actingAs($admin)->getJson(route('admin.overview.stats', ['period' => 'daily']))->assertOk();

        $this->record(SiteEvent::VISIT, now());
        $this->record(SiteEvent::CLICK, now());
        $this->record(SiteEvent::CLICK, now());
        Order::create([
            'user_id' => $customer->id, 'order_type' => 'normal', 'shipping_name' => 'T', 'shipping_address' => 'T', 'total_amount' => 2500,
            'order_date' => now(), 'order_status' => 'confirmed', 'payment_method' => 'credit_card', 'payment_status' => 'paid',
            'tracking_number' => 'RVL-STATS-' . uniqid(),
        ]);

        $after = $this->actingAs($admin)->getJson(route('admin.overview.stats', ['period' => 'daily']))
            ->assertOk()
            ->assertJsonPath('period', 'daily');

        $this->assertSame($before->json('websiteVisits') + 1, $after->json('websiteVisits'));
        $this->assertSame($before->json('totalClicks') + 2, $after->json('totalClicks'));
        $this->assertSame($before->json('totalOrders') + 1, $after->json('totalOrders'));
        $this->assertEquals($before->json('revenue') + 2500, $after->json('revenue'));
        $this->assertCount(7, $after->json('series.visits'));
        $this->assertSame($before->json('series.visits.6') + 1, $after->json('series.visits.6'), 'today is the last slot');
        $this->assertEquals($before->json('series.sales.6') + 2500, $after->json('series.sales.6'));

        $this->getJson(route('admin.overview.stats', ['period' => 'weekly']))->assertOk()->assertJsonCount(8, 'series.visits');
        $this->getJson(route('admin.overview.stats', ['period' => 'monthly']))->assertOk()->assertJsonCount(12, 'series.visits');
        $this->getJson(route('admin.overview.stats', ['period' => 'bogus']))->assertOk()->assertJsonPath('series', null);
    }

    public function test_only_admins_reach_the_stats_endpoint_and_polling_leaves_the_session_cookie_alone(): void
    {
        $this->getJson(route('admin.overview.stats'))->assertUnauthorized();
        $this->actingAs(User::factory()->create())->getJson(route('admin.overview.stats'))->assertForbidden();

        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))->getJson(route('admin.overview.stats'))->assertOk();
        $this->assertSame([], $response->headers->getCookies(), 'a background poll must not overwrite the admin\'s session cookie');
    }

    private function record(string $type, Carbon $at): void
    {
        SiteEvent::unguarded(fn () => SiteEvent::create([
            'type' => $type,
            'visit_id' => Str::random(32),
            'created_at' => $at,
        ]));
    }
}
