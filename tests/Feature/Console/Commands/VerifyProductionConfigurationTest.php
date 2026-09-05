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
        'sentry.dsn' => null,
        'sentry.send_default_pii' => false,
    ]);
});

test('safe production configuration passes', function (): void {
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
            'SENTRY_SEND_DEFAULT_PII must be false.',
        )
        ->not->toContain('admin@example.test');
});

test('enabled Sentry requires production environment and a release identifier', function (): void {
    config()->set([
        'sentry.dsn' => 'https://public-key@example.ingest.sentry.io/123',
        'sentry.environment' => 'staging',
        'sentry.release' => null,
    ]);

    $exitCode = Artisan::call('app:verify-production');
    $output = Artisan::output();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($output)->toContain(
            'SENTRY_ENVIRONMENT must be production when Sentry is enabled.',
            'SENTRY_RELEASE must be configured when Sentry is enabled.',
        )
        ->not->toContain('public-key');
});
