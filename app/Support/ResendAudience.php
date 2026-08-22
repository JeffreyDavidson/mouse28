<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ResendAudience
{
    private const CACHE_KEY = 'newsletter_subscribers';

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

    /** @return array{subscribers: list<array<string, mixed>>, error: ?string} */
    private function fetch(): array
    {
        $audienceId = config('services.resend.audience_id');

        if (! is_string($audienceId) || blank($audienceId)) {
            return ['subscribers' => [], 'error' => 'The Resend audience is not configured.'];
        }

        try {
            $response = Http::withToken((string) config('services.resend.key'))
                ->timeout(10)
                ->get("https://api.resend.com/audiences/{$audienceId}/contacts");
        } catch (\Throwable) {
            return ['subscribers' => [], 'error' => 'Could not connect to the Resend API.'];
        }

        if (! $response->successful()) {
            return [
                'subscribers' => [],
                'error' => 'Failed to fetch subscribers from Resend API (HTTP '.$response->status().').',
            ];
        }

        $subscribers = $this->normalize($response->json('data', []));

        Cache::put(self::CACHE_KEY, $subscribers, now()->addMinutes(5));

        return ['subscribers' => $subscribers, 'error' => null];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalize(mixed $subscribers): array
    {
        if (! is_array($subscribers)) {
            return [];
        }

        return array_values(array_filter($subscribers, is_array(...)));
    }
}
