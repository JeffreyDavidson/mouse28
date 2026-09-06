<?php

use App\Enums\GuideCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Support\ContentContinuation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->freezeSecond();
});

test('related guides prioritize category and respect the requested limit', function (int $limit): void {
    $current = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    $olderMatch = Guide::factory()->create([
        'category' => GuideCategory::Accessibility,
        'published_at' => now()->subDays(4),
    ]);
    $newerMatch = Guide::factory()->create([
        'category' => GuideCategory::Accessibility,
        'published_at' => now()->subDays(3),
    ]);
    $fallback = Guide::factory()->create([
        'category' => GuideCategory::FamilyPlanning,
        'published_at' => now()->subDay(),
    ]);
    Guide::factory()->create([
        'category' => GuideCategory::FamilyPlanning,
        'published_at' => now()->subDays(2),
    ]);
    Guide::factory()->draft()->create(['category' => GuideCategory::Accessibility]);
    Guide::factory()->scheduled()->create(['category' => GuideCategory::Accessibility]);
    $deleted = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    $deleted->delete();

    $related = ContentContinuation::relatedGuides($current, $limit);

    expect($related->modelKeys())->toBe(array_slice([$newerMatch->id, $olderMatch->id, $fallback->id], 0, $limit));
})->with([
    'one matching guide' => [1],
    'matching category fills limit' => [2],
    'fallback fills remaining slot' => [3],
]);

test('related guides return an empty collection when no other published guide exists', function (): void {
    $current = Guide::factory()->create();
    Guide::factory()->draft()->create();
    Guide::factory()->scheduled()->create();

    $related = ContentContinuation::relatedGuides($current);

    expect($related)->toBeEmpty();
});

test('episode neighbors are the nearest published dates rather than episode numbers', function (): void {
    $current = Episode::factory()->create(['published_at' => now()->subDays(3), 'episode_number' => 10]);
    $previous = Episode::factory()->create(['published_at' => now()->subDays(4), 'episode_number' => 20]);
    $next = Episode::factory()->create(['published_at' => now()->subDays(2), 'episode_number' => 5]);
    Episode::factory()->create(['published_at' => now()->subDays(5), 'episode_number' => 9]);
    Episode::factory()->create(['published_at' => now()->subDay(), 'episode_number' => 11]);
    Episode::factory()->draft()->create(['published_at' => now()->subHours(73), 'episode_number' => 12]);
    Episode::factory()->draft()->create(['published_at' => now()->subHours(71), 'episode_number' => 13]);
    $deleted = Episode::factory()->create(['published_at' => now()->subHours(70), 'episode_number' => 14]);
    $deleted->delete();

    $previousEpisode = ContentContinuation::previousEpisode($current);
    $nextEpisode = ContentContinuation::nextEpisode($current);

    expect($previousEpisode?->id)->toBe($previous->id)
        ->and($nextEpisode?->id)->toBe($next->id);
});

test('episode navigation returns null at the published collection boundaries', function (): void {
    $current = Episode::factory()->create();
    Episode::factory()->draft()->create();
    Episode::factory()->scheduled()->create();

    $previous = ContentContinuation::previousEpisode($current);
    $next = ContentContinuation::nextEpisode($current);

    expect($previous)->toBeNull()
        ->and($next)->toBeNull();
});

test('an episode without a publication date has no chronological neighbors', function (): void {
    $current = Episode::factory()->draft()->make();
    Episode::factory()->create();

    $previous = ContentContinuation::previousEpisode($current);
    $next = ContentContinuation::nextEpisode($current);

    expect($previous)->toBeNull()
        ->and($next)->toBeNull();
});
