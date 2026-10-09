<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\ViewModels\EpisodeIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

covers(EpisodeIndexViewModel::class);

pest()->use(RefreshDatabase::class);

test('episode index data uses stable ID ordering for equal publication dates', function (): void {
    $episodes = Episode::factory()
        ->count(2)
        ->create(['published_at' => now()->subDay()]);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->getCollection()
        ->pluck('id')
        ->all())->toBe($episodes->reverse()
        ->pluck('id')
        ->all());
});

test('episode index data includes published episodes and distribution data', function (): void {
    $published = Episode::factory()->create(['title' => 'Published episode']);
    $draft = Episode::factory()
        ->draft()
        ->create(['title' => 'Draft episode']);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->getCollection()
        ->pluck('id')
        ->all())
        ->toContain($published->id)
        ->not->toContain($draft->id)
        ->and($data['podcastLinks'])
        ->toBeArray()
        ->and($data['canonicalUrl'])
        ->toBe(route('episodes.index'));
});

test('episode index data uses the configured page size', function (): void {
    config()->set('mouse28.episodes_per_page', 2);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->perPage())->toBe(2);
});

test('episode index data names the newest episode and groups the page by season', function (): void {
    $oldest = Episode::factory()->create(['season_number' => 1, 'published_at' => now()->subDays(3)]);
    $unseasoned = Episode::factory()->create(['season_number' => null, 'published_at' => now()->subDays(2)]);
    $newest = Episode::factory()->create(['season_number' => 1, 'published_at' => now()->subDay()]);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['latestEpisode']?->is($newest))->toBeTrue()
        ->and($data['seasons']->map(fn (Collection $episodes): array => $episodes->pluck('id')
            ->all())
            ->all())
        ->toBe([1 => [$newest->id, $oldest->id], 0 => [$unseasoned->id]]);
});

test('episode index data has no newest episode or seasons without episodes', function (): void {
    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['latestEpisode'])->toBeNull()
        ->and($data['seasons'])
        ->toBeEmpty();
});

test('episode index data uses the bundled cover without an uploaded one', function (): void {
    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['coverImage'])->toBe('/images/podcast/mouse28-cover.webp')
        ->and($data['shareImage'])
        ->toBe('/images/podcast/mouse28-cover.jpg')
        ->and($data['coverSrcset'])
        ->toContain('/images/podcast/mouse28-cover-640.webp 640w');
});

test('episode index data uses the uploaded podcast cover', function (): void {
    Podcast::factory()->create(['cover_image_path' => 'podcast/cover.png']);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['coverImage'])->toBe('/storage/podcast/cover.png')
        ->and($data['shareImage'])
        ->toBe('/storage/podcast/cover.png');
});
