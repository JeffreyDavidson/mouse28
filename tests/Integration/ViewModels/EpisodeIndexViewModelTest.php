<?php

use App\Models\Episode;
use App\ViewModels\EpisodeIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(EpisodeIndexViewModel::class);

pest()->use(RefreshDatabase::class);

test('episode index data uses stable ID ordering for equal publication dates', function (): void {
    $episodes = Episode::factory()->count(2)->create(['published_at' => now()->subDay()]);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->getCollection()->pluck('id')->all())->toBe($episodes->reverse()->pluck('id')->all());
});

test('episode index data includes published episodes and distribution data', function (): void {
    $published = Episode::factory()->create(['title' => 'Published episode']);
    $draft = Episode::factory()->draft()->create(['title' => 'Draft episode']);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->getCollection()->pluck('id')->all())
        ->toContain($published->id)
        ->not->toContain($draft->id)
        ->and($data['podcastLinks'])->toBeArray()
        ->and($data['canonicalUrl'])->toBe(route('episodes.index'));
});

test('episode index data uses the configured page size', function (): void {
    config()->set('mouse28.episodes_per_page', 2);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->perPage())->toBe(2);
});
