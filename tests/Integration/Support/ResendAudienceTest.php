<?php

use App\Support\ResendAudience;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.resend.key', 'resend-test-key');
    Cache::forget('newsletter_subscribers');
});

test('a successful audience read is shared through the cache', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response([
            'data' => [['email' => 'reader@example.com', 'created_at' => '2026-08-22T12:00:00Z']],
        ]),
    ]);

    $audience = app(ResendAudience::class);

    expect($audience->get()['subscribers'])->toHaveCount(1)
        ->and($audience->get()['error'])->toBeNull();

    Http::assertSentCount(1);
});
