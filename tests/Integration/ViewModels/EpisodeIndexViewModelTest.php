<?php

use App\Models\Episode;
use App\ViewModels\EpisodeIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('episode index data includes published episodes and distribution data', function (): void {
    $published = Episode::factory()->create(['title' => 'Published episode']);
    $draft = Episode::factory()->draft()->create(['title' => 'Draft episode']);

    $data = app(EpisodeIndexViewModel::class)->data();

    expect($data['episodes']->getCollection()->modelKeys())
        ->toContain($published->id)
        ->not->toContain($draft->id)
        ->and($data['podcastLinks'])->toBeArray()
        ->and($data['canonicalUrl'])->toBe(route('episodes.index'));
});
