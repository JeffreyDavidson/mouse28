<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\from;

uses(RefreshDatabase::class);

test('contact errors and old input stay out of the newsletter form', function (): void {
    from(route('contact.show'))
        ->followingRedirects()
        ->post(route('contact.store'), [
            'name' => 'Dale Cooper',
            'email' => 'not-an-email',
            'subject' => 'general',
            'message' => 'Please help with this park question.',
            'cf-turnstile-response' => 'unused-token',
        ])
        ->assertOk()
        ->assertSee('id="email" name="email" required autocomplete="email" inputmode="email" value="not-an-email"', false)
        ->assertSee('id="footer-newsletter-email" type="email" name="email" value=""', false)
        ->assertSee('aria-describedby="email-error"', false)
        ->assertDontSee('aria-describedby="newsletter-email-error"', false);
});

test('newsletter errors and old input stay out of the contact form', function (): void {
    from(route('contact.show'))
        ->followingRedirects()
        ->post(route('newsletter.store'), [
            'email' => 'not-an-email',
            'cf-turnstile-response' => 'unused-token',
        ])
        ->assertOk()
        ->assertSee('id="email" name="email" required autocomplete="email" inputmode="email" value=""', false)
        ->assertSee('id="footer-newsletter-email" type="email" name="email" value="not-an-email"', false)
        ->assertDontSee('aria-describedby="email-error"', false);
});
