<?php

use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

test('subscriber statistics count only explicitly active contacts', function (): void {
    config()->set('services.resend.audience_id', 'audience-test-id');
    Cache::forget('newsletter_subscribers');
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => [
            ['email' => 'active@example.com', 'unsubscribed' => false],
            ['email' => 'unsubscribed@example.com', 'unsubscribed' => true],
            ['email' => 'unknown@example.com'],
            ['email' => 'invalid@example.com', 'unsubscribed' => 'false'],
        ]]),
    ]);

    $stat = collect(app(StatsOverview::class)->getStats())->sole('label', 'Subscribers');

    expect($stat['value'])->toBe(1)
        ->and($stat['description'])->toBe('Active newsletter subscribers');
});

test('published statistics exclude scheduled content', function (): void {
    config()->set('services.resend.audience_id', 'audience-test-id');
    Cache::forget('newsletter_subscribers');
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    Post::factory()->create();
    Post::factory()->scheduled()->create();
    Post::factory()->draft()->create();
    Episode::factory()->create();
    Episode::factory()->scheduled()->create();
    Episode::factory()->draft()->create();
    Guide::factory()->create();
    Guide::factory()->scheduled()->create();
    Guide::factory()->draft()->create();

    $stats = collect(app(StatsOverview::class)->getStats())->pluck('value', 'label');

    expect($stats['Blog Posts'])->toBe(1)
        ->and($stats['Episodes'])->toBe(1)
        ->and($stats['Guides'])->toBe(1)
        ->and($stats['Drafts'])->toBe(3);
});

test('dashboard signals when a sourced published post is due for review', function (): void {
    config()->set('mouse28.post_review_interval_days', 180);
    Post::factory()->create([
        'source_url' => 'https://example.test/official-source',
        'last_reviewed_at' => Date::today()->subDays(181),
    ]);

    $stat = collect(app(StatsOverview::class)->getStats())->sole('label', 'Blog Posts');

    expect($stat['description'])->toBe('1 need review');
});
