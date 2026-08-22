<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\from;

uses(RefreshDatabase::class);

test('contact errors and old input stay out of the newsletter form', function (): void {
    $response = from(route('contact.show'))
        ->followingRedirects()
        ->post(route('contact.store'), [
            'name' => 'Dale Cooper',
            'email' => 'not-an-email',
            'subject' => 'general',
            'message' => 'Please help with this park question.',
            'cf-turnstile-response' => 'unused-token',
        ])
        ->assertOk();

    expect($response->getContent())
        ->toMatch('/<input\s+type="email"\s+id="email"\s+name="email"\s+required\s+autocomplete="email"\s+inputmode="email"\s+value="not-an-email"/')
        ->toMatch('/<input\s+id="footer-newsletter-email"\s+type="email"\s+name="email"\s+value=""/');

    $response
        ->assertSee('aria-describedby="email-error"', false)
        ->assertDontSee('aria-describedby="newsletter-email-error"', false);
});

test('newsletter errors and old input stay out of the contact form', function (): void {
    $response = from(route('contact.show'))
        ->followingRedirects()
        ->post(route('newsletter.store'), [
            'email' => 'not-an-email',
            'cf-turnstile-response' => 'unused-token',
        ])
        ->assertOk();

    expect($response->getContent())
        ->toMatch('/<input\s+type="email"\s+id="email"\s+name="email"\s+required\s+autocomplete="email"\s+inputmode="email"\s+value=""/')
        ->toMatch('/<input\s+id="footer-newsletter-email"\s+type="email"\s+name="email"\s+value="not-an-email"/');

    $response
        ->assertDontSee('aria-describedby="email-error"', false);
});
