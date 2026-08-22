<?php

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    config()->set('services.turnstile.site_key', 'test-site-key');
    config()->set('services.turnstile.secret_key', 'test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.contact_action', 'contact-form');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com', 'www.mouse28.com']);
});

test('contact page renders turnstile widget', function (): void {
    $response = get(route('contact.show'))
        ->assertOk()
        ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false)
        ->assertSee('class="cf-turnstile"', false)
        ->assertSee('data-sitekey="test-site-key"', false)
        ->assertSee('data-action="contact-form"', false)
        ->assertDontSee('Share Your Story')
        ->assertDontSee('Family Disney stories')
        ->assertDontSee('value="story"', false);

    expect(substr_count((string) $response->getContent(), 'https://challenges.cloudflare.com/turnstile/v0/api.js'))->toBe(1);
    expect(ContactMessage::SUBJECTS)->not->toHaveKey('story');
});

test('valid contact submission requires successful turnstile verification', function (): void {
    Mail::fake();

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'mouse28.com',
        ]),
    ]);

    $response = from(route('contact.show'))
        ->post(route('contact.store'), contactPayload());

    $response->assertRedirect(route('contact.show'))
        ->assertSessionHas('success', true)
        ->assertSessionHasNoErrors();

    assertDatabaseHas('contact_messages', [
        'email' => 'dale@example.com',
        'subject' => 'Need help with Mouse28',
    ]);

    Mail::assertSent(ContactFormSubmitted::class);
    Mail::assertSent(ContactFormConfirmation::class);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'turnstile-token';
    });
});

test('contact submission rejects failed turnstile verification before persistence or mail', function (): void {
    Mail::fake();

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
    ]);

    from(route('contact.show'))
        ->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'cf-turnstile-response');

    assertDatabaseCount('contact_messages', 0);
    Mail::assertNothingSent();
});

test('contact submission rejects invalid turnstile metadata before persistence or mail', function (array $turnstileResponse, string $email): void {
    Mail::fake();

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($turnstileResponse),
    ]);

    from(route('contact.show'))
        ->post(route('contact.store'), array_merge(contactPayload(), ['email' => $email]))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'cf-turnstile-response');

    assertDatabaseCount('contact_messages', 0);
    Mail::assertNothingSent();
})->with([
    'wrong hostname' => [
        ['success' => true, 'action' => 'contact-form', 'hostname' => 'attacker.example'],
        'wrong-hostname@example.com',
    ],
    'missing hostname' => [
        ['success' => true, 'action' => 'contact-form'],
        'missing-hostname@example.com',
    ],
    'wrong action' => [
        ['success' => true, 'action' => 'newsletter', 'hostname' => 'mouse28.com'],
        'wrong-action@example.com',
    ],
    'missing action' => [
        ['success' => true, 'hostname' => 'mouse28.com'],
        'missing-action@example.com',
    ],
]);

test('contact submission rejects missing turnstile secret before persistence or mail', function (): void {
    Mail::fake();
    config()->set('services.turnstile.secret_key');

    from(route('contact.show'))
        ->post(route('contact.store'), contactPayload())
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'cf-turnstile-response');

    assertDatabaseCount('contact_messages', 0);
    Mail::assertNothingSent();
    Http::assertNothingSent();
});

test('honeypot silently accepts bot submissions without persistence or mail', function (): void {
    Mail::fake();

    from(route('contact.show'))
        ->post(route('contact.store'), array_merge(contactPayload(), [
            'website_url' => 'https://spam.example',
        ]))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHas('success', true);

    assertDatabaseCount('contact_messages', 0);
    Mail::assertNothingSent();
    Http::assertNothingSent();
});

test('contact form rate limit is applied without throttling the contact page', function (): void {
    Mail::fake();

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'www.mouse28.com',
        ]),
    ]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        from(route('contact.show'))
            ->post(route('contact.store'), array_merge(contactPayload(), [
                'email' => "dale{$attempt}@example.com",
            ]))
            ->assertSessionHasNoErrors();
    }

    from(route('contact.show'))
        ->post(route('contact.store'), array_merge(contactPayload(), [
            'email' => 'dale-rate-limit@example.com',
        ]))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'contact_rate_limit');

    get(route('contact.show'))->assertOk();
});

function contactPayload(): array
{
    return [
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => 'Need help with Mouse28',
        'message' => 'The contact form needs secure bot protection.',
        'cf-turnstile-response' => 'turnstile-token',
    ];
}
