<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Terms of Service, Privacy Policy and Shipping & Returns are three separate pages, each
 * linked from the footer that every storefront page carries. Rolled back.
 */
class PolicyPagesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_each_policy_is_its_own_page(): void
    {
        $this->get('/terms')->assertOk()->assertSee('Terms of Service')->assertSee('21. Contact Us')
            ->assertDontSee('Cash on Delivery');

        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy')->assertSee('14. Contact Us')
            ->assertDontSee('Cash on Delivery');

        $this->get('/shipping')->assertOk()->assertSee('Shipping &amp; Returns Policy', false)->assertSee('10. Contact Us')
            ->assertSee('Ravellen does not offer Cash on Delivery');
    }

    public function test_the_footer_links_to_all_three_from_any_page(): void
    {
        foreach (['/', '/about', '/terms'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('href="' . route('terms') . '"', false)
                ->assertSee('href="' . route('privacy') . '"', false)
                ->assertSee('href="' . route('shipping') . '"', false);
        }
    }

    public function test_policies_point_at_each_other(): void
    {
        $this->get('/terms')
            ->assertSee('href="' . route('shipping') . '"', false)
            ->assertSee('href="' . route('privacy') . '"', false);

        $this->get('/privacy')->assertSee('href="' . route('shipping') . '"', false);
    }

    public function test_the_tab_marks_the_page_you_are_on(): void
    {
        $this->get('/privacy')->assertSeeInOrder(['policy-tab active', 'Privacy Policy'], false);
        $this->assertSame(1, substr_count($this->get('/privacy')->getContent(), 'policy-tab active'));
    }
}
