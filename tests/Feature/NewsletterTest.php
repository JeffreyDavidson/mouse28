<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\from;
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
        ->assertSee('name="website_url"', false);
});

test('valid newsletter signup is sent to configured resend audience', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], 201),
    ]);

    from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', true)
        ->assertSessionHasNoErrors();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.resend.com/audiences/audience-test-id/contacts'
        && $request['email'] === 'dale@example.com');
});

test('newsletter signup rejects invalid turnstile response', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', 'cf-turnstile-response');

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'api.resend.com'));
});

test('newsletter honeypot silently accepts bot without external requests', function (): void {
    Http::fake();

    from(route('home'))
        ->post(route('newsletter.store'), array_merge(newsletterPayload(), [
            'website_url' => 'https://spam.example',
        ]))
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', true);

    Http::assertNothingSent();
});

test('newsletter requires a configured audience', function (): void {
    config()->set('services.resend.audience_id');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_error');

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'api.resend.com'));
});

test('newsletter rate limit is applied', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/*' => Http::response([], 201),
    ]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        from(route('home'))
            ->post(route('newsletter.store'), array_merge(newsletterPayload(), [
                'email' => "dale{$attempt}@example.com",
            ]))
            ->assertSessionHasNoErrors();
    }

    from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload())
        ->assertSessionHasErrorsIn('newsletter', 'newsletter_rate_limit');
});

test('newsletter redirects do not trust an external referrer', function (): void {
    config()->set('services.resend.audience_id');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    from('https://attacker.example/phish')
        ->post(route('newsletter.store'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter');
});

function newsletterPayload(): array
{
    return [
        'email' => 'dale@example.com',
        'cf-turnstile-response' => 'turnstile-token',
    ];
}
