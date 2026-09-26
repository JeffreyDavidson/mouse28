<?php

use App\Http\Requests\StoreNewsletterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\from;
use function Pest\Laravel\postJson;

covers(StoreNewsletterRequest::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.resend.enabled', true);
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

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.resend.com/audiences/audience-test-id/contacts'
        && $request['email'] === 'dale@example.com');
});

test('valid newsletter signup returns a successful JSON response', function (): void {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], 201),
    ]);

    postJson(route('newsletter.store'), newsletterPayload())
        ->assertOk()
        ->assertExactJson(['success' => true]);
});

test('newsletter signup returns unavailable when the resend integration is disabled', function (): void {
    config()->set('services.resend.enabled', false);
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'newsletter',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    postJson(route('newsletter.store'), newsletterPayload())
        ->assertStatus(503)
        ->assertExactJson(['error' => 'Something went wrong.']);

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.resend.com'));
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
        ->toMatch('/<input(?=[^>]*\bid="email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="")[^>]*>/')
        ->toMatch('/<input(?=[^>]*\bid="footer-newsletter-email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="not-an-email")[^>]*>/');

    $response->assertDontSeeHtml('aria-describedby="email-error"');
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

    postJson(route('newsletter.store'), newsletterPayload())
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

    postJson(route('newsletter.store'), newsletterPayload())
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

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.resend.com'));
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

    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.resend.com'));
});

test('newsletter rate limit ignores spoofed forwarded IPs', function (): void {
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
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->withHeaders(['X-Forwarded-For' => "203.0.113.{$attempt}"])
            ->post(route('newsletter.store'), array_merge(newsletterPayload(), [
                'email' => "dale{$attempt}@example.com",
            ]))
            ->assertSessionHasNoErrors();
    }

    from(route('home'))
        ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
        ->withHeaders(['X-Forwarded-For' => '203.0.113.99'])
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

/** @return array<string, string> */
function newsletterPayload(): array
{
    return [
        'email' => 'dale@example.com',
        'cf-turnstile-response' => 'turnstile-token',
    ];
}

test('newsletter rate limit uses the configured attempts per minute', function (): void {
    config()->set('mouse28.rate_limits.newsletter_per_minute', 1);
    $payload = array_merge(newsletterPayload(), ['website_url' => 'https://spam.example']);

    from(route('home'))
        ->post(route('newsletter.store'), $payload)
        ->assertSessionHasNoErrors();

    from(route('home'))
        ->post(route('newsletter.store'), $payload)
        ->assertSessionHasErrorsIn('newsletter', 'newsletter_rate_limit');
});
