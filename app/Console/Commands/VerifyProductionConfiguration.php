<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:verify-production')]
#[Description('Verify that required production settings are safely configured')]
class VerifyProductionConfiguration extends Command
{
    public function handle(): int
    {
        $sentryEnabled = $this->isConfigured(config('sentry.dsn'));
        $appUrl = config('app.url');
        $canonicalUrl = config('mouse28.production_url');

        $checks = [
            [config('app.env') === 'production', 'APP_ENV must be production.'],
            [config('app.debug') === false, 'APP_DEBUG must be false.'],
            [$this->isCanonicalHttpsUrl($appUrl, $canonicalUrl), 'APP_URL must use the canonical HTTPS URL.'],
            [$this->isConfigured(config('app.key')), 'APP_KEY must be configured.'],
            [config('session.secure') === true, 'SESSION_SECURE_COOKIE must be true.'],
            [$this->usesPersistentDriver(config('session.driver')), 'SESSION_DRIVER must use a persistent driver.'],
            [$this->usesPersistentDriver(config('cache.default')), 'CACHE_STORE must use a persistent driver.'],
            [$this->isConfigured(config('database.default')), 'DB_CONNECTION must be configured.'],
            [$this->isConfigured(config('filesystems.default')), 'FILESYSTEM_DISK must be configured.'],
            [$this->usesDeliveringMailer(config('mail.default')), 'MAIL_MAILER must use a delivering transport.'],
            [$this->isProductionEmail(config('mail.from.address')), 'MAIL_FROM_ADDRESS must use a production address.'],
            [$this->isProductionEmail(config('mail.admin_address')), 'MAIL_ADMIN_ADDRESS must use a monitored production address.'],
            [$this->isConfigured(config('services.resend.key')), 'RESEND_API_KEY must be configured.'],
            [$this->isConfigured(config('services.resend.audience_id')), 'RESEND_AUDIENCE_ID must be configured.'],
            [$this->isConfigured(config('services.turnstile.site_key')), 'TURNSTILE_SITE_KEY must be configured.'],
            [$this->isConfigured(config('services.turnstile.secret_key')), 'TURNSTILE_SECRET_KEY must be configured.'],
            [$this->allowsCanonicalHost(config('services.turnstile.allowed_hostnames'), $canonicalUrl), 'TURNSTILE_ALLOWED_HOSTNAMES must include the canonical host.'],
            [$this->isTransistorFeedUrl(config('podcast.rss_url')), 'PODCAST_RSS_URL must use a Transistor feed URL.'],
            [config('sentry.send_default_pii') === false, 'SENTRY_SEND_DEFAULT_PII must be false.'],
            [! $sentryEnabled || config('sentry.environment') === 'production', 'SENTRY_ENVIRONMENT must be production when Sentry is enabled.'],
            [! $sentryEnabled || $this->isConfigured(config('sentry.release')), 'SENTRY_RELEASE must be configured when Sentry is enabled.'],
        ];

        $failures = array_map(
            fn (array $check): string => $check[1],
            array_filter($checks, fn (array $check): bool => ! $check[0]),
        );

        if ($failures !== []) {
            $this->error('Production configuration is not ready:');

            foreach ($failures as $failure) {
                $this->line(" - {$failure}");
            }

            return self::FAILURE;
        }

        $this->info('Production configuration is ready.');

        return self::SUCCESS;
    }

    private function isCanonicalHttpsUrl(mixed $url, mixed $canonicalUrl): bool
    {
        return is_string($url)
            && is_string($canonicalUrl)
            && $url === $canonicalUrl
            && str_starts_with($url, 'https://');
    }

    private function isConfigured(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    private function usesPersistentDriver(mixed $driver): bool
    {
        return is_string($driver) && ! in_array($driver, ['array', 'null'], true);
    }

    private function usesDeliveringMailer(mixed $mailer): bool
    {
        return is_string($mailer) && ! in_array($mailer, ['array', 'log'], true);
    }

    private function isProductionEmail(mixed $email): bool
    {
        return is_string($email)
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ! str_ends_with($email, '@example.com')
            && ! str_ends_with($email, '@example.test');
    }

    private function isTransistorFeedUrl(mixed $url): bool
    {
        return is_string($url)
            && parse_url($url, PHP_URL_SCHEME) === 'https'
            && parse_url($url, PHP_URL_HOST) === 'feeds.transistor.fm'
            && filled(trim((string) parse_url($url, PHP_URL_PATH), '/'));
    }

    private function allowsCanonicalHost(mixed $allowedHostnames, mixed $canonicalUrl): bool
    {
        if (! is_array($allowedHostnames) || ! is_string($canonicalUrl)) {
            return false;
        }

        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);

        return is_string($canonicalHost) && in_array($canonicalHost, $allowedHostnames, true);
    }
}
