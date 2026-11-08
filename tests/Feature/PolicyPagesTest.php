<?php

namespace Tests\Feature;

use App\Models\Setting;
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

        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy')->assertSee('15. Contact Us')
            ->assertDontSee('Cash on Delivery');

        $this->get('/shipping')->assertOk()->assertSee('Shipping &amp; Returns Policy', false)->assertSee('10. Contact Us')
            ->assertSee('Ravellen does not offer Cash on Delivery');
    }

    public function test_contact_sections_carry_the_real_instagram_and_whatsapp_details(): void
    {
        Setting::set('support_phone', '+92 343 5587925');
        $instagram = 'href="https://www.instagram.com/ravellen_nm"';
        $whatsapp  = 'href="https://wa.me/923435587925"';

        // privacy lists Instagram only; shipping & returns lists both; terms lists both
        $this->get('/privacy')->assertOk()
            ->assertSee($instagram, false)->assertSee('@ravellen_nm')
            ->assertDontSee($whatsapp, false);

        foreach (['/shipping', '/terms'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee($instagram, false)->assertSee('@ravellen_nm')
                ->assertSee($whatsapp, false)->assertSee('+92 343 5587925');
        }

        foreach (['/privacy', '/shipping', '/terms'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('[Insert Instagram', false)
                ->assertDontSee('[Insert WhatsApp', false)
                ->assertDontSee('[Insert Business Email', false);
        }
    }

    public function test_all_three_policies_name_the_live_website(): void
    {
        foreach (['/privacy', '/terms'] as $url) {
            $this->get($url)->assertOk()->assertSee('href="https://ravellenraynae.store"', false);
        }

        // shipping & returns points at the contact page of the same site
        $this->get('/shipping')->assertOk()->assertSee('href="https://ravellenraynae.store/contact"', false);

        foreach (['/privacy', '/shipping', '/terms'] as $url) {
            $this->get($url)->assertDontSee('ravellen.test', false);
        }
    }

    public function test_privacy_policy_discloses_google_sign_in_and_google_user_data(): void
    {
        Setting::set('support_email', 'ravellenraynae@gmail.com');

        $html = $this->get('/privacy')->assertOk()
            ->assertSee('13. Google Sign-In and Google User Data')
            ->assertSee('14. Changes to This Privacy Policy')
            ->assertSee('15. Contact Us')
            ->assertSeeInOrder([
                'Information We Collect from Google',
                'How We Use Google User Data',
                'Sharing of Google User Data',
                'Storage and Security of Google User Data',
                'Data Retention and Deletion',
                'Google Permissions',
            ])
            ->assertSee("uses Google's OAuth authentication service to verify your identity.", false)
            ->assertSee('We do not sell, rent, or trade Google user data. We do not use Google user data for advertising purposes.')
            ->assertSee('We do not request access to Gmail, Google Drive, Google Contacts, or other Google services unless explicitly disclosed and requested through a separate permission.')
            ->assertSee('href="mailto:ravellenraynae@gmail.com"', false)
            ->getContent();

        // the section is part of the privacy policy only
        $this->get('/terms')->assertOk()->assertDontSee('Google User Data');
        $this->assertStringContainsString('Associate your Google account with your Ravellen account.', $html);
    }

    public function test_the_whatsapp_link_follows_the_saved_phone_even_in_local_format(): void
    {
        Setting::set('support_phone', '0300 1112223');

        $this->get('/shipping')->assertOk()
            ->assertSee('href="https://wa.me/923001112223"', false)
            ->assertSee('0300 1112223');
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
