<?php

use App\Enums\ContactTopic;
use App\Http\Requests\StoreContactRequest;
use App\Jobs\SendContactMessageEmails;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\get;

covers(StoreContactRequest::class);

pest()->use(RefreshDatabase::class);

test('contact page displays its view model data', function (): void {
    config()->set('mouse28.contact.email', 'contact@example.test');

    get(route('contact.show'))
        ->assertOk()
        ->assertViewIs('pages.contact')
        ->assertViewHas('contactEmail', 'contact@example.test')
        ->assertViewHas('contactFormAvailable', true);
});

test('contact stays within its query budget', function (): void {
    $this->expectsDatabaseQueryCount(1);

    get(route('contact.show'))
        ->assertOk();
});

beforeEach(function (): void {
    Bus::fake([SendContactMessageEmails::class]);
    config()->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
    config()->set('services.turnstile.site_key', 'test-site-key');
    config()->set('services.turnstile.secret_key', 'test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.contact_action', 'contact-form');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com', 'www.mouse28.com']);
    config()->set('mouse28.contact.email', 'contact@mouse28.test');
});

test('contact page renders turnstile widget', function (): void {
    $response = get(route('contact.show'))->assertOk()->assertSeeHtml('https://challenges.cloudflare.com/turnstile/v0/api.js')->assertSeeHtml('class="cf-turnstile"')->assertSeeHtml('data-sitekey="test-site-key"')->assertSeeHtml('data-action="contact-form"')->assertSeeHtml('data-appearance="interaction-only"')
        ->assertSee('Park Accessibility Question')
        ->assertSee('Guest on the Podcast')
        ->assertDontSee('Share Your Story')->assertDontSee('Family Disney stories')->assertDontSeeHtml('value="story"');

    expect(substr_count((string) $response->getContent(), 'https://challenges.cloudflare.com/turnstile/v0/api.js'))->toBe(1)
        ->and(array_column(ContactTopic::cases(), 'value'))->not->toContain('story');
});

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
        ->toMatch('/<input(?=[^>]*\bid="email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="not-an-email")[^>]*>/')
        ->toMatch('/<input(?=[^>]*\bid="footer-newsletter-email")(?=[^>]*\btype="email")(?=[^>]*\bvalue="")[^>]*>/');

    $response->assertSeeHtml('aria-describedby="email-error"')->assertDontSeeHtml('aria-describedby="newsletter-email-error"');
});

test('contact page uses the configured site contact email address', function (): void {
    config()->set('mouse28.contact.email', 'hello@mouse28.test');

    get(route('contact.show'))->assertOk()->assertSeeHtml('href="mailto:hello@mouse28.test"')
        ->assertSee('hello@mouse28.test');
});

test('contact page offers email instead of an unusable form when verification is unavailable', function (string $missingKey): void {
    config()->set("services.turnstile.{$missingKey}");
    config()->set('mouse28.contact.email', 'fallback@mouse28.test');

    get(route('contact.show'))
        ->assertOk()
        ->assertViewHas('contactFormAvailable', false)
        ->assertSee('Email us directly')
        ->assertSeeHtml('href="mailto:fallback@mouse28.test"')
        ->assertDontSeeHtml('action="'.route('contact.store').'"')
        ->assertDontSeeHtml('data-action="contact-form"');
})->with([
    'missing site key' => 'site_key',
    'missing secret key' => 'secret_key',
]);

test('valid contact submission stores the message and queues delivery', function (): void {
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
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => 'Need help with Mouse28',
        'message' => 'The contact form needs secure bot protection.',
    ]);

    Mail::assertNothingSent();
    Bus::assertDispatched(SendContactMessageEmails::class);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request['secret'] === 'test-secret-key'
        && $request['response'] === 'turnstile-token');
});

test('contact submission rejects invalid input before verification or persistence', function (): void {
    Http::fake();

    from(route('contact.show'))
        ->post(route('contact.store'), array_merge(contactPayload(), [
            'email' => 'not-an-email',
        ]))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'email');

    assertDatabaseCount('contact_messages', 0);
    Http::assertNothingSent();
});

test('contact submission rejects missing required fields before verification or persistence', function (string $field): void {
    Http::fake();

    from(route('contact.show'))
        ->post(route('contact.store'), array_merge(contactPayload(), [$field => '']))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', $field);

    assertDatabaseCount('contact_messages', 0);
    Http::assertNothingSent();
})->with(['name', 'subject', 'message']);

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

test('contact form rate limit ignores spoofed forwarded IPs without throttling the contact page', function (): void {
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
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->withHeaders(['X-Forwarded-For' => "203.0.113.{$attempt}"])
            ->post(route('contact.store'), array_merge(contactPayload(), [
                'email' => "dale{$attempt}@example.com",
            ]))
            ->assertSessionHasNoErrors();
    }

    from(route('contact.show'))
        ->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
        ->withHeaders(['X-Forwarded-For' => '203.0.113.99'])
        ->post(route('contact.store'), array_merge(contactPayload(), [
            'email' => 'dale-rate-limit@example.com',
        ]))
        ->assertRedirect(route('contact.show'))
        ->assertSessionHasErrorsIn('contact', 'contact_rate_limit');

    get(route('contact.show'))->assertOk();
});

test('contact page renders', function (): void {
    get(route('contact.show'))
        ->assertOk()
        ->assertSee('Send us a note');
});

test('contact page exposes SEO metadata', function (): void {
    get(route('contact.show'))->assertOk()->assertSeeHtml('<meta name="description" content="Contact Jeffrey and Cassie about Mouse28, Disney park accessibility, family travel, collaborations, or the podcast.">');
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('contact.show'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('contact.show'))->assertOk()->assertSeeHtml('data-brand-wordmark')->assertSeeHtml('dispatch-letter-form')->assertSeeHtml('js-dispatch-pages');
});

test('form placeholders use readable text colors', function (): void {
    config()->set('services.turnstile.site_key', 'test-site-key');
    config()->set('services.turnstile.secret_key', 'test-secret-key');

    get(route('contact.show'))->assertOk()->assertSeeHtml('placeholder:text-navy/65')->assertDontSeeHtml('placeholder:text-navy/30');
});

/** @return array<string, string> */
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

test('contact form rate limit uses the configured attempts per minute', function (): void {
    config()->set('mouse28.rate_limits.contact_form_per_minute', 1);
    $payload = array_merge(contactPayload(), ['website_url' => 'https://spam.example']);

    from(route('contact.show'))
        ->post(route('contact.store'), $payload)
        ->assertSessionHasNoErrors();

    from(route('contact.show'))
        ->post(route('contact.store'), $payload)
        ->assertSessionHasErrorsIn('contact', 'contact_rate_limit');
});

test('contact page exposes a single main landmark without nested complementary regions', function (): void {
    $response = get(route('contact.show'))->assertOk();

    $document = HTMLDocument::createFromString($this->responseContent($response), LIBXML_NOERROR);
    $xpath = new XPath($document);

    expect($xpath->query('//*[local-name()="main"]'))->toHaveCount(1)
        ->and($xpath->query('//*[local-name()="main"]//*[local-name()="aside"]'))->toBeEmpty();
});
