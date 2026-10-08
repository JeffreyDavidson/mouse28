<?php

use App\Filament\Widgets\StatsOverview;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

test('subscriber statistics count only confirmed readers who have not unsubscribed', function (): void {
    Subscriber::factory()
        ->count(2)
        ->create();
    Subscriber::factory()
        ->pending()
        ->create();
    Subscriber::factory()
        ->unsubscribed()
        ->create();

    $stat = collect(app(StatsOverview::class)->getStats())->sole('label', 'Subscribers');

    expect($stat['value'])->toBe(2)
        ->and($stat['description'])
        ->toBe('Active newsletter subscribers');
});

test('published statistics exclude scheduled content', function (): void {
    Post::factory()->create();
    Post::factory()
        ->scheduled()
        ->create();
    Post::factory()
        ->draft()
        ->create();
    Episode::factory()->create();
    Episode::factory()
        ->scheduled()
        ->create();
    Episode::factory()
        ->draft()
        ->create();
    Guide::factory()->create();
    Guide::factory()
        ->scheduled()
        ->create();
    Guide::factory()
        ->draft()
        ->create();

    $stats = collect(app(StatsOverview::class)->getStats())->pluck('value', 'label');

    expect($stats['Blog Posts'])->toBe(1)
        ->and($stats['Episodes'])
        ->toBe(1)
        ->and($stats['Guides'])
        ->toBe(1)
        ->and($stats['Drafts'])
        ->toBe(3);
});

test('dashboard signals when a sourced published post is due for review', function (): void {
    config()->set('content.post_review_interval_days', 180);
    Post::factory()->create([
        'source_url' => 'https://example.test/official-source',
        'last_reviewed_at' => Date::today()->subDays(181),
    ]);

    $stat = collect(app(StatsOverview::class)->getStats())->sole('label', 'Blog Posts');

    expect($stat['description'])->toBe('1 need review');
});
