<?php

use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

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

    $stats = collect(app(StatsOverview::class)->getStats())->keyBy('label');

    expect($stats['Blog Posts']['value'])->toBe(1)
        ->and($stats['Episodes']['value'])->toBe(1)
        ->and($stats['Guides']['value'])->toBe(1)
        ->and($stats['Drafts']['value'])->toBe(3);
});
