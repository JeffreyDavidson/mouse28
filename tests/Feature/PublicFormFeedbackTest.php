<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFormFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_errors_and_old_input_stay_out_of_the_newsletter_form(): void
    {
        $response = $this->from(route('contact.show'))
            ->followingRedirects()
            ->post(route('contact.store'), [
                'name' => 'Dale Cooper',
                'email' => 'not-an-email',
                'subject' => 'general',
                'message' => 'Please help with this park question.',
                'cf-turnstile-response' => 'unused-token',
            ]);

        $response->assertOk()
            ->assertSee('id="email" name="email" required autocomplete="email" inputmode="email" value="not-an-email"', false)
            ->assertSee('id="footer-newsletter-email" type="email" name="email" value=""', false)
            ->assertSee('aria-describedby="email-error"', false)
            ->assertDontSee('aria-describedby="newsletter-email-error"', false);
    }

    public function test_newsletter_errors_and_old_input_stay_out_of_the_contact_form(): void
    {
        $response = $this->from(route('contact.show'))
            ->followingRedirects()
            ->post(route('newsletter.store'), [
                'email' => 'not-an-email',
                'cf-turnstile-response' => 'unused-token',
            ]);

        $response->assertOk()
            ->assertSee('id="email" name="email" required autocomplete="email" inputmode="email" value=""', false)
            ->assertSee('id="footer-newsletter-email" type="email" name="email" value="not-an-email"', false)
            ->assertDontSee('aria-describedby="email-error"', false);
    }
}
