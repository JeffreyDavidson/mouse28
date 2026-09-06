<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);
});

test('newsletter forms render bot protection', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('data-action="newsletter"', false)
        ->assertSee('data-appearance="interaction-only"', false)
        ->assertSee('name="website_url"', false);
});
