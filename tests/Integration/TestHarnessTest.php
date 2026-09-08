<?php

use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Http;

test('the test harness rejects unfaked requests', function (): void {
    // Arrange
    expect(Http::preventingStrayRequests())->toBeTrue();
    $exception = null;

    // Act
    try {
        Http::get('https://unfaked.example.invalid');
    } catch (StrayRequestException $caught) {
        $exception = $caught;
    }

    // Assert
    expect($exception)->toBeInstanceOf(StrayRequestException::class);
});

test('the test harness allows explicitly faked requests', function (): void {
    // Arrange
    Http::fake(['example.invalid/*' => Http::response(['ok' => true])]);

    // Act
    $response = Http::get('https://example.invalid/check');

    // Assert
    expect($response->json('ok'))->toBeTrue();
    Http::assertSentCount(1);
});

test('test credentials are empty', function (string $key): void {
    // Act
    $isEmpty = blank(config($key));

    // Assert
    expect($isEmpty)->toBeTrue();
})->with([
    'services.resend.key',
    'services.resend.audience_id',
    'services.turnstile.site_key',
    'services.turnstile.secret_key',
    'services.fathom.site_id',
    'sentry.dsn',
    'nightwatch.token',
]);

test('tests use non-delivering mail and disabled monitoring', function (): void {
    // Act
    $mailer = config('mail.default');
    $nightwatch = config('nightwatch.enabled');

    // Assert
    expect($mailer)->toBe('array')
        ->and($nightwatch)->toBeFalse();
});
