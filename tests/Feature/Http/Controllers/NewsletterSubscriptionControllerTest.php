<?php

use App\Actions\RequestNewsletterSubscription;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Mail\NewsletterConfirmationMail;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\from;

covers(NewsletterSubscriptionController::class, SubscribeNewsletterRequest::class, RequestNewsletterSubscription::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    Mail::fake();
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);
});

/** @return array<string, string> */
function newsletterPayload(): array
{
    return [
        'email' => 'Dale@Example.com',
        'cf-turnstile-response' => 'turnstile-token',
    ];
}

function fakeTurnstile(string $action = 'newsletter'): void
{
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => $action,
            'hostname' => 'mouse28.com',
        ]),
    ]);
}

test('newsletter sign-up stores a pending reader and queues the confirmation email', function (): void {
    fakeTurnstile();

    from(route('home'))
        ->post(route('newsletter.subscribe'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your sign-up.')
        ->assertSessionHasNoErrors();

    $reader = Subscriber::query()->sole();

    expect($reader->email)->toBe('dale@example.com')
        ->and($reader->isActive())
        ->toBeFalse();

    Mail::assertQueued(NewsletterConfirmationMail::class, fn (NewsletterConfirmationMail $mail): bool => $mail->hasTo('dale@example.com'));
});

test('newsletter sign-up looks the same for an address that is already active', function (): void {
    fakeTurnstile();
    Subscriber::factory()->create(['email' => 'dale@example.com']);

    from(route('home'))
        ->post(route('newsletter.subscribe'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your sign-up.');

    assertDatabaseCount('subscribers', 1);
    Mail::assertNothingQueued();
});

test('newsletter sign-up rejects an invalid turnstile response', function (): void {
    fakeTurnstile('contact-form');

    from(route('home'))
        ->post(route('newsletter.subscribe'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', 'cf-turnstile-response');

    assertDatabaseCount('subscribers', 0);
    Mail::assertNothingQueued();
});

test('newsletter sign-up rejects an invalid email and keeps what was typed', function (): void {
    from(route('home'))
        ->post(route('newsletter.subscribe'), ['email' => 'not-an-email', 'cf-turnstile-response' => 'unused-token'])
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHasErrorsIn('newsletter', 'email')
        ->assertSessionHasInput('email', 'not-an-email');

    assertDatabaseCount('subscribers', 0);
});

test('newsletter errors and old input stay out of the contact form', function (): void {
    $response = from(route('contact.create'))
        ->followingRedirects()
        ->post(route('newsletter.subscribe'), [
            'email' => 'not-an-email',
            'cf-turnstile-response' => 'unused-token',
        ])
        ->assertOk();

    expect($response->getContent())
        ->toMatch('/<input(?=[^>]*\bid="email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="")[^>]*>/')
        ->toMatch('/<input(?=[^>]*\bid="footer-newsletter-email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="not-an-email")[^>]*>/');

    $response->assertDontSeeHtml('aria-describedby="email-error"');
});

test('newsletter honeypot silently accepts a bot without storing or sending anything', function (): void {
    Http::fake();

    from(route('home'))
        ->post(route('newsletter.subscribe'), array_merge(newsletterPayload(), ['website_url' => 'https://spam.example']))
        ->assertRedirect(route('home').'#newsletter')
        ->assertSessionHas('newsletter_success', 'Check your email to confirm your sign-up.');

    assertDatabaseCount('subscribers', 0);
    Http::assertNothingSent();
    Mail::assertNothingQueued();
});

test('newsletter rate limit ignores spoofed forwarded IPs', function (): void {
    fakeTurnstile();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        from(route('home'))
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->withHeaders(['X-Forwarded-For' => "203.0.113.{$attempt}"])
            ->post(route('newsletter.subscribe'), array_merge(newsletterPayload(), ['email' => "dale{$attempt}@example.com"]))
            ->assertSessionHasNoErrors();
    }

    from(route('home'))
        ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
        ->withHeaders(['X-Forwarded-For' => '203.0.113.99'])
        ->post(route('newsletter.subscribe'), newsletterPayload())
        ->assertSessionHasErrorsIn('newsletter', 'newsletter_rate_limit');
});

test('newsletter rate limit uses the configured attempts per minute', function (): void {
    config()->set('mouse28.rate_limits.newsletter_per_minute', 1);
    $payload = array_merge(newsletterPayload(), ['website_url' => 'https://spam.example']);

    from(route('home'))
        ->post(route('newsletter.subscribe'), $payload)
        ->assertSessionHasNoErrors();

    from(route('home'))
        ->post(route('newsletter.subscribe'), $payload)
        ->assertSessionHasErrorsIn('newsletter', 'newsletter_rate_limit');
});

test('newsletter redirects do not trust an external referrer', function (): void {
    fakeTurnstile();

    from('https://attacker.example/phish')
        ->post(route('newsletter.subscribe'), newsletterPayload())
        ->assertRedirect(route('home').'#newsletter');
});
