<?php

namespace App\Support;

use App\Enums\NewsletterSubscriptionResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendAudience
{
    private const string CACHE_KEY = 'newsletter_subscribers';

    /** @return array{subscribers: list<array<string, mixed>>, error: ?string} */
    public function get(): array
    {
        $subscribers = Cache::get(self::CACHE_KEY);

        if (is_array($subscribers)) {
            return ['subscribers' => $this->normalize($subscribers), 'error' => null];
        }

        return $this->fetch();
    }

    /** @return array{subscribers: list<array<string, mixed>>, error: ?string} */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->fetch();
    }

    public function subscribe(string $email): NewsletterSubscriptionResult
    {
        $audienceId = config('services.resend.audience_id');

        if (! is_string($audienceId) || blank($audienceId)) {
            Log::error('Newsletter signup is missing its Resend audience configuration.');

            return NewsletterSubscriptionResult::ConfigurationMissing;
        }

        try {
            $response = Http::withToken(Config::string('services.resend.key'))
                ->timeout(10)
                ->post("https://api.resend.com/audiences/{$audienceId}/contacts", [
                    'email' => $email,
                ]);
        } catch (\Throwable $exception) {
            Log::error('Newsletter signup request failed', [
                'exception' => $exception::class,
            ]);

            return NewsletterSubscriptionResult::ConnectionFailed;
        }

        if (! $response->successful()) {
            Log::warning('Resend newsletter signup failed', [
                'status' => $response->status(),
            ]);

            return NewsletterSubscriptionResult::ProviderRejected;
        }

        Cache::forget(self::CACHE_KEY);

        return NewsletterSubscriptionResult::Subscribed;
    }

    /** @return array{subscribers: list<array<string, mixed>>, error: ?string} */
    private function fetch(): array
    {
        $audienceId = config('services.resend.audience_id');

        if (! is_string($audienceId) || blank($audienceId)) {
            return ['subscribers' => [], 'error' => 'The Resend audience is not configured.'];
        }

        $subscribers = [];
        $cursor = null;
        $seenCursors = [];

        for ($page = 0; $page < 100; $page++) {
            try {
                $response = Http::withToken(Config::string('services.resend.key'))
                    ->connectTimeout(3)
                    ->timeout(10)
                    ->get("https://api.resend.com/audiences/{$audienceId}/contacts", array_filter([
                        'limit' => 100,
                        'after' => $cursor,
                    ]));
            } catch (\Throwable) {
                return ['subscribers' => [], 'error' => 'Could not connect to the Resend API.'];
            }

            if (! $response->successful()) {
                return ['subscribers' => [], 'error' => 'Failed to fetch subscribers from Resend API (HTTP '.$response->status().').'];
            }

            $batch = $this->normalize($response->json('data', []));
            $subscribers = [...$subscribers, ...$batch];

            if ($response->json('has_more') !== true) {
                Cache::put(self::CACHE_KEY, $subscribers, Date::now()->addMinutes(5));

                return ['subscribers' => $subscribers, 'error' => null];
            }

            $cursor = $batch === [] ? null : array_last($batch)['id'] ?? null;
            if (! is_string($cursor) || $cursor === '' || in_array($cursor, $seenCursors, true)) {
                return ['subscribers' => [], 'error' => 'The Resend API returned an invalid pagination cursor.'];
            }
            $seenCursors[] = $cursor;
        }

        return ['subscribers' => [], 'error' => 'The audience exceeds the supported retrieval limit.'];

    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalize(mixed $subscribers): array
    {
        if (! is_array($subscribers)) {
            return [];
        }

        $normalized = [];

        foreach ($subscribers as $subscriber) {
            if (! is_array($subscriber)) {
                continue;
            }

            $fields = [];

            foreach ($subscriber as $key => $value) {
                if (is_string($key)) {
                    $fields[$key] = $value;
                }
            }

            $normalized[] = $fields;
        }

        return $normalized;
    }
}
