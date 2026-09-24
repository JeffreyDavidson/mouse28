<?php

use App\Enums\NewsletterSubscriptionResult;
use App\Support\ResendAudience;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

covers(ResendAudience::class);

beforeEach(function (): void {
    config()->set('services.resend.enabled', true);
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Cache::forget('newsletter_subscribers');
});

test('disabled integration never exposes cached or remote subscribers', function (): void {
    Cache::put('newsletter_subscribers', [['email' => 'cached@example.com']], now()->addMinutes(5));
    config()->set('services.resend.enabled', false);
    Http::fake();

    $result = app(ResendAudience::class)->get();

    expect($result)->toBe([
        'subscribers' => [],
        'error' => 'The Resend integration is disabled.',
    ])->and(Cache::has('newsletter_subscribers'))->toBeFalse();
    Http::assertNothingSent();
});

test('disabled integration clears cached subscribers when explicitly refreshed', function (): void {
    Cache::put('newsletter_subscribers', [['email' => 'cached@example.com']], now()->addMinutes(5));
    config()->set('services.resend.enabled', false);
    Http::fake();

    $result = app(ResendAudience::class)->refresh();

    expect($result['subscribers'])->toBeEmpty()
        ->and($result['error'])->toBe('The Resend integration is disabled.')
        ->and(Cache::has('newsletter_subscribers'))->toBeFalse();
    Http::assertNothingSent();
});

test('all subscriber pages are retrieved before caching', function (): void {
    Http::fakeSequence()
        ->push(['data' => [['id' => 'one', 'email' => 'first@example.com']], 'has_more' => true])
        ->push(['data' => [['id' => 'two', 'email' => 'second@example.com']], 'has_more' => false]);

    $result = app(ResendAudience::class)->get();

    expect($result['subscribers'])->toHaveCount(2);
    Http::assertSent(fn (Request $request): bool => ($request->data()['after'] ?? null) === 'one');
    expect(app(ResendAudience::class)->get())->toBe($result);
    Http::assertSentCount(2);
});

test('a later provider failure never caches or exports a partial audience', function (): void {
    Http::fakeSequence()
        ->push(['data' => [['id' => 'one', 'email' => 'first@example.com']], 'has_more' => true])
        ->push([], 503);

    $result = app(ResendAudience::class)->get();

    expect($result['subscribers'])->toBeEmpty()
        ->and($result['error'])->not->toBeNull()
        ->and(Cache::has('newsletter_subscribers'))->toBeFalse();
});

test('a successful audience read is shared through the cache', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response([
            'data' => [['email' => 'reader@example.com', 'created_at' => '2026-08-22T12:00:00Z']],
        ]),
    ]);

    $audience = app(ResendAudience::class);

    $firstRead = $audience->get();
    $cachedRead = $audience->get();

    expect($firstRead)->toBe([
        'subscribers' => [['email' => 'reader@example.com', 'created_at' => '2026-08-22T12:00:00Z']],
        'error' => null,
    ])->and($cachedRead)->toBe($firstRead);

    Http::assertSentCount(1);
});

test('audience normalization keeps named fields and excludes malformed entries', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => [
            null,
            'invalid',
            ['email' => 'reader@example.com', 0 => 'unexpected', 'unsubscribed' => true],
        ]]),
    ]);
    $audience = app(ResendAudience::class);

    $result = $audience->get();
    $cachedResult = $audience->get();

    expect($result)->toBe([
        'subscribers' => [['email' => 'reader@example.com', 'unsubscribed' => true]],
        'error' => null,
    ])->and($cachedResult)->toBe($result);
    Http::assertSentCount(1);
});

test('missing audience configuration does not contact the provider', function (?string $audienceId): void {
    config()->set('services.resend.audience_id', $audienceId);
    Http::fake();
    $audience = app(ResendAudience::class);

    $result = $audience->get();

    expect($result)->toBe([
        'subscribers' => [],
        'error' => 'The Resend audience is not configured.',
    ]);
    Http::assertNothingSent();
})->with([
    'missing' => [null],
    'empty' => [''],
    'whitespace' => ['   '],
]);

test('provider errors are reported and the next read retries', function (): void {
    Http::fakeSequence()
        ->push(['message' => 'Provider unavailable'], 503)
        ->push(['data' => [['email' => 'recovered@example.com']]]);
    $audience = app(ResendAudience::class);

    $failedRead = $audience->get();
    $recoveredRead = $audience->get();

    expect($failedRead)->toBe([
        'subscribers' => [],
        'error' => 'Failed to fetch subscribers from Resend API (HTTP 503).',
    ])->and($recoveredRead)->toBe([
        'subscribers' => [['email' => 'recovered@example.com']],
        'error' => null,
    ]);
    Http::assertSentCount(2);
});

test('connection failures return a safe error without caching an empty audience', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::failedConnection('Private connection details'),
    ]);
    $audience = app(ResendAudience::class);

    $result = $audience->get();

    expect($result)->toBe([
        'subscribers' => [],
        'error' => 'Could not connect to the Resend API.',
    ])->and(Cache::has('newsletter_subscribers'))->toBeFalse();
});

test('a successful empty audience is cached without an error', function (): void {
    Http::fakeSequence()
        ->push(['data' => []]);
    $audience = app(ResendAudience::class);

    $firstRead = $audience->get();
    $cachedRead = $audience->get();

    expect($firstRead)->toBe(['subscribers' => [], 'error' => null])
        ->and($cachedRead)->toBe($firstRead);
    Http::assertSentCount(1);
});

test('audience data is fetched again after the five minute cache expires', function (): void {
    $this->freezeSecond();
    Http::fakeSequence()
        ->push(['data' => [['email' => 'first@example.com']]])
        ->push(['data' => [['email' => 'updated@example.com']]]);
    $audience = app(ResendAudience::class);

    $firstRead = $audience->get();
    $this->travel(299)
        ->seconds();
    $cachedRead = $audience->get();
    $this->travel(2)
        ->seconds();
    $freshRead = $audience->get();

    expect($firstRead)->toBe([
        'subscribers' => [['email' => 'first@example.com']],
        'error' => null,
    ])->and($cachedRead)->toBe($firstRead)
        ->and($freshRead)->toBe([
            'subscribers' => [['email' => 'updated@example.com']],
            'error' => null,
        ]);
    Http::assertSentCount(2);
});

test('a successful subscription adds the contact and invalidates cached audience data', function (): void {
    Cache::put('newsletter_subscribers', [['email' => 'existing@example.com']], now()->addMinutes(5));
    Http::fake([
        'https://api.resend.com/audiences/audience-test-id/contacts' => Http::response([], 201),
    ]);

    $result = app(ResendAudience::class)->subscribe('reader@example.com');

    expect($result)->toBe(NewsletterSubscriptionResult::Subscribed)
        ->and(Cache::has('newsletter_subscribers'))->toBeFalse();
    Http::assertSent(fn (Request $request): bool => $request['email'] === 'reader@example.com');
});

test('disabled integration rejects newsletter signup without contacting the provider', function (): void {
    config()->set('services.resend.enabled', false);
    Http::fake();

    $result = app(ResendAudience::class)->subscribe('reader@example.com');

    expect($result)->toBe(NewsletterSubscriptionResult::Disabled);
    Http::assertNothingSent();
});

test('subscription failures distinguish configuration provider and connection errors', function (
    string $scenario,
    NewsletterSubscriptionResult $expected,
): void {
    if ($scenario === 'missing configuration') {
        config()->set('services.resend.audience_id');
        Http::fake();
    } elseif ($scenario === 'provider rejection') {
        Http::fake([
            'https://api.resend.com/*' => Http::response([], 503),
        ]);
    } else {
        Http::fake([
            'https://api.resend.com/*' => Http::failedConnection(),
        ]);
    }

    $result = app(ResendAudience::class)->subscribe('reader@example.com');

    expect($result)->toBe($expected);
})->with([
    'missing configuration' => ['missing configuration', NewsletterSubscriptionResult::ConfigurationMissing],
    'provider rejection' => ['provider rejection', NewsletterSubscriptionResult::ProviderRejected],
    'connection failure' => ['connection failure', NewsletterSubscriptionResult::ConnectionFailed],
]);
