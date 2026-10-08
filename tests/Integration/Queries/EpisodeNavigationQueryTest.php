<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Queries\EpisodeNavigationQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(EpisodeNavigationQuery::class);

beforeEach(function (): void {
    $this->freezeSecond();
});

test('episode navigation uses IDs to break equal publication dates', function (): void {
    $previous = Episode::factory()->create(['published_at' => now()->subDay()]);
    $current = Episode::factory()->create(['published_at' => $previous->published_at]);
    $next = Episode::factory()->create(['published_at' => $previous->published_at]);

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation['previous']?->id)->toBe($previous->id)
        ->and($navigation['next']?->id)
        ->toBe($next->id);
});

test('episode navigation finds the nearest published dates rather than episode numbers', function (): void {
    $current = Episode::factory()->create(['published_at' => now()->subDays(3), 'episode_number' => 10]);
    $previous = Episode::factory()->create(['published_at' => now()->subDays(4), 'episode_number' => 20]);
    $next = Episode::factory()->create(['published_at' => now()->subDays(2), 'episode_number' => 5]);
    Episode::factory()->create(['published_at' => now()->subDays(5), 'episode_number' => 9]);
    Episode::factory()->create(['published_at' => now()->subDay(), 'episode_number' => 11]);
    Episode::factory()
        ->draft()
        ->create(['published_at' => now()->subHours(73), 'episode_number' => 12]);
    Episode::factory()
        ->draft()
        ->create(['published_at' => now()->subHours(71), 'episode_number' => 13]);
    $deleted = Episode::factory()->create(['published_at' => now()->subHours(70), 'episode_number' => 14]);
    $deleted->delete();

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation['previous']?->id)->toBe($previous->id)
        ->and($navigation['next']?->id)
        ->toBe($next->id);
});

test('episode navigation stays within the episode podcast', function (): void {
    $podcast = Podcast::factory()->create();
    $otherPodcast = Podcast::factory()->create();
    $current = Episode::factory()
        ->for($podcast)
        ->create(['published_at' => now()->subDays(3)]);
    $previous = Episode::factory()
        ->for($podcast)
        ->create(['published_at' => now()->subDays(5)]);
    $next = Episode::factory()
        ->for($podcast)
        ->create(['published_at' => now()->subDay()]);
    Episode::factory()
        ->for($otherPodcast)
        ->create(['published_at' => now()->subDays(4)]);
    Episode::factory()
        ->for($otherPodcast)
        ->create(['published_at' => now()->subDays(2)]);

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation['previous']?->id)->toBe($previous->id)
        ->and($navigation['next']?->id)
        ->toBe($next->id);
});

test('episode navigation returns null at the published collection boundaries', function (): void {
    $current = Episode::factory()->create();
    Episode::factory()
        ->draft()
        ->create();
    Episode::factory()
        ->scheduled()
        ->create();

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation)->toBe([
        'previous' => null,
        'next' => null,
    ]);
});

test('an episode without a publication date has no chronological neighbors', function (): void {
    $current = Episode::factory()
        ->draft()
        ->make();
    Episode::factory()->create();

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation)->toBe([
        'previous' => null,
        'next' => null,
    ]);
});

test('episode navigation selects only the fields rendered by its links', function (): void {
    $current = Episode::factory()->create(['published_at' => now()->subDay()]);
    Episode::factory()->create(['published_at' => now()->subDays(2)]);
    Episode::factory()->create(['published_at' => now()]);

    $navigation = app(EpisodeNavigationQuery::class)->get($current);

    expect($navigation['previous']?->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title'])
        ->not->toHaveKeys(['description', 'show_notes', 'transcript'])
        ->and($navigation['next']?->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title'])
        ->not->toHaveKeys(['description', 'show_notes', 'transcript']);
});

test('episode navigation breaks publish-time ties by id so its order is stable on MySQL', function (): void {
    $episode = Episode::factory()->create();

    $orderings = publishTimeOrderings(fn () => app(EpisodeNavigationQuery::class)->get($episode));

    expect($orderings)->toHaveCount(2)
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
