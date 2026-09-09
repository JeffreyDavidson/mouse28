<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\from;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);
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

test('newsletter preserves the submitted email after a resend HTTP failure', function (int $providerStatus): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], $providerStatus),
    ]);

    $response = from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload());

    $response->assertRedirect(route('home').'#newsletter');
    $response->assertSessionHas('newsletter_error', 'Something went wrong. Please try again.');
    $response->assertSessionHasInput('email', 'dale@example.com');
    $response->assertSessionMissing('newsletter_success');
})->with([
    'client error' => [422],
    'server error' => [503],
]);

test('newsletter preserves the submitted email after a resend connection failure', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::failedConnection(),
    ]);

    $response = from(route('home'))
        ->post(route('newsletter.store'), newsletterPayload());

    $response->assertRedirect(route('home').'#newsletter');
    $response->assertSessionHas('newsletter_error', 'Something went wrong. Please try again.');
    $response->assertSessionHasInput('email', 'dale@example.com');
    $response->assertSessionMissing('newsletter_success');
});

test('newsletter returns a safe JSON response after a resend HTTP failure', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], 503),
    ]);

    $this->postJson(route('newsletter.store'), newsletterPayload())
        ->assertUnprocessable()
        ->assertExactJson(['error' => 'Something went wrong.']);
});

test('newsletter returns a safe JSON response after a resend connection failure', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::failedConnection(),
    ]);

    $this->postJson(route('newsletter.store'), newsletterPayload())
        ->assertServerError()
        ->assertExactJson(['error' => 'Something went wrong.']);
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
