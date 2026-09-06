<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    config()->set([
        'app.env' => 'production',
        'app.debug' => false,
        'app.url' => 'https://mouse28.com',
        'app.key' => 'base64:production-key',
        'mouse28.production_url' => 'https://mouse28.com',
        'mouse28.deployment_environment' => 'production',
        'session.secure' => true,
        'session.driver' => 'database',
        'cache.default' => 'database',
        'database.default' => 'sqlite',
        'filesystems.default' => 'local',
        'mail.default' => 'resend',
        'mail.from.address' => 'hello@mouse28.com',
        'mail.admin_address' => 'admin@mouse28.com',
        'services.resend.key' => 'resend-production-key',
        'services.resend.audience_id' => 'audience-id',
        'services.turnstile.site_key' => 'turnstile-site-key',
        'services.turnstile.secret_key' => 'turnstile-secret-key',
        'services.turnstile.allowed_hostnames' => ['mouse28.com', 'www.mouse28.com'],
        'podcast.rss_url' => 'https://feeds.transistor.fm/mouse28',
        'nightwatch.enabled' => true,
        'nightwatch.token' => 'nightwatch-production-token',
        'nightwatch.capture_request_payload' => false,
        'nightwatch.sampling.requests' => 0.1,
        'sentry.dsn' => 'https://public-key@example.ingest.sentry.io/123',
        'sentry.environment' => 'production',
        'sentry.release' => 'production-release',
        'sentry.traces_sample_rate' => 0.0,
        'sentry.profiles_sample_rate' => 0.0,
        'sentry.send_default_pii' => false,
    ]);
});

test('safe production configuration passes', function (): void {
    $exitCode = Artisan::call('app:verify-production');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Production configuration is ready.');
});

test('safe staging configuration passes with isolated observability', function (): void {
    config()->set([
        'app.url' => 'https://staging.mouse28.com',
        'mouse28.production_url' => 'https://staging.mouse28.com',
        'mouse28.deployment_environment' => 'staging',
        'services.turnstile.allowed_hostnames' => ['staging.mouse28.com'],
        'sentry.environment' => 'staging',
        'sentry.release' => 'staging-release',
    ]);

    $exitCode = Artisan::call('app:verify-production');

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('Production configuration is ready.');
});

test('unsafe production configuration reports every failure without exposing values', function (): void {
    config()->set([
        'app.debug' => true,
        'app.url' => 'http://localhost',
        'session.secure' => false,
        'session.driver' => 'array',
        'cache.default' => 'array',
        'mail.default' => 'log',
        'mail.from.address' => 'hello@example.com',
        'mail.admin_address' => 'admin@example.test',
        'services.resend.key' => null,
        'services.resend.audience_id' => null,
        'services.turnstile.site_key' => null,
        'services.turnstile.secret_key' => null,
        'services.turnstile.allowed_hostnames' => ['localhost'],
        'podcast.rss_url' => 'https://example.com/mouse28.xml',
        'nightwatch.enabled' => false,
        'nightwatch.token' => null,
        'nightwatch.capture_request_payload' => true,
        'nightwatch.sampling.requests' => 1.0,
        'sentry.dsn' => null,
        'sentry.environment' => null,
        'sentry.release' => null,
        'sentry.traces_sample_rate' => 0.1,
        'sentry.profiles_sample_rate' => 0.1,
        'sentry.send_default_pii' => true,
    ]);

    $exitCode = Artisan::call('app:verify-production');
    $output = Artisan::output();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output)->toContain(
            'Production configuration is not ready:',
            'APP_DEBUG must be false.',
            'APP_URL must use the canonical HTTPS URL.',
            'SESSION_SECURE_COOKIE must be true.',
            'SESSION_DRIVER must use a persistent driver.',
            'CACHE_STORE must use a persistent driver.',
            'MAIL_MAILER must use a delivering transport.',
            'RESEND_API_KEY must be configured.',
            'TURNSTILE_SECRET_KEY must be configured.',
            'TURNSTILE_ALLOWED_HOSTNAMES must include the canonical host.',
            'PODCAST_RSS_URL must use a Transistor feed URL.',
            'NIGHTWATCH_ENABLED must be true.',
            'NIGHTWATCH_TOKEN must be configured.',
            'NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD must be false.',
            'NIGHTWATCH_REQUEST_SAMPLE_RATE must be greater than 0 and no more than 0.1.',
            'SENTRY_LARAVEL_DSN must be configured.',
            'SENTRY_SEND_DEFAULT_PII must be false.',
            'SENTRY_ENVIRONMENT must match MOUSE28_DEPLOYMENT_ENVIRONMENT.',
            'SENTRY_RELEASE must be configured.',
            'SENTRY_TRACES_SAMPLE_RATE must be 0.0 until tracing is deliberately enabled.',
            'SENTRY_PROFILES_SAMPLE_RATE must be 0.0 until profiling is deliberately enabled.',
        )
        ->not->toContain('admin@example.test');
});

test('observability credentials are never exposed in verification output', function (): void {
    config()->set([
        'nightwatch.token' => 'private-nightwatch-token',
        'sentry.dsn' => 'https://private-public-key@example.ingest.sentry.io/123',
        'sentry.environment' => 'staging',
        'sentry.release' => null,
    ]);

    $exitCode = Artisan::call('app:verify-production');
    $output = Artisan::output();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output)->toContain(
            'SENTRY_ENVIRONMENT must match MOUSE28_DEPLOYMENT_ENVIRONMENT.',
            'SENTRY_RELEASE must be configured.',
        )
        ->not->toContain(
            'private-nightwatch-token',
            'private-public-key',
        );
});
