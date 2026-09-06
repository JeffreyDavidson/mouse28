<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\from;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.turnstile.site_key', 'test-site-key');
    config()->set('services.turnstile.secret_key', 'test-secret-key');
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
