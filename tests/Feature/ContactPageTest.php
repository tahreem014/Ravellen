<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The contact page and the footer show the support email and phone saved in Admin → Settings, the
 * footer links only Instagram and TikTok, and the contact form mails its message to the support
 * email. Rolled back; no real mail is sent.
 */
class ContactPageTest extends TestCase
{
    use DatabaseTransactions;

    private array $message = [
        'name'    => 'Sara Khan',
        'email'   => 'sara@example.com',
        'message' => 'Hi! Can I add a preference to my mystery scoop order?',
    ];

    public function test_the_shop_details_are_set_to_the_real_email_and_phone(): void
    {
        $this->assertSame('ravellenraynae@gmail.com', Setting::get('support_email'));
        $this->assertSame('+92 343 5587925', Setting::get('support_phone'));
    }

    public function test_contact_page_and_footer_show_the_saved_email_and_phone(): void
    {
        Setting::set('support_email', 'shop@example.com');
        Setting::set('support_phone', '+92 343 5587925');

        foreach (['/contact', '/about'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('href="mailto:shop@example.com"', false)
                ->assertSee('href="tel:+923435587925"', false)
                ->assertSee('+92 343 5587925')
                ->assertDontSee('info@ravellen.com')
                ->assertDontSee('hello@ravellen.com')
                ->assertDontSee('1234567');
        }
    }

    public function test_footer_links_only_instagram_and_tiktok(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('href="https://www.instagram.com/ravellen_nm"', false)
            ->assertSee('href="https://www.tiktok.com/@ravellen_nm"', false)
            ->assertSee('ri-instagram-line', false)
            ->assertSee('ri-tiktok-line', false)
            ->assertDontSee('ri-facebook-fill', false)
            ->assertDontSee('ri-twitter-x-line', false);
    }

    public function test_contact_page_lists_the_faqs(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('Where does Ravellen deliver?')
            ->assertSee('Do you accept pre-orders?');
    }

    public function test_the_form_mails_the_message_to_the_support_email(): void
    {
        Mail::fake();
        Setting::set('support_email', 'shop@example.com');

        $this->from('/contact')->post(route('contact.send'), $this->message)
            ->assertRedirect('/contact')
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            $html = $mail->render();   // builds the mailable, which is what applies its subject and reply-to

            return $mail->hasTo('shop@example.com')
                && $mail->hasReplyTo('sara@example.com')
                && $mail->hasSubject('New message from Sara Khan — Ravellen contact form')
                && str_contains($html, 'Can I add a preference to my mystery scoop order?')
                && str_contains($html, 'Sara Khan');
        });
        Mail::assertSent(ContactMessageMail::class, 1);
    }

    public function test_an_incomplete_or_invalid_message_is_not_sent(): void
    {
        Mail::fake();

        $this->from('/contact')->post(route('contact.send'), ['name' => '', 'email' => 'not-an-email', 'message' => 'short'])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_tells_the_visitor_and_keeps_what_they_typed(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('SMTP is down'));

        $this->from('/contact')->post(route('contact.send'), $this->message)
            ->assertRedirect('/contact')
            ->assertSessionHas('error')
            ->assertSessionHasInput('message', $this->message['message']);
    }
}
