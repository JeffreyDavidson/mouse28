<?php

use App\Models\Episode;
use App\Models\Post;
use App\ViewModels\EpisodeViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('episode data includes related published posts', function (): void {
    $episode = Episode::factory()->create();
    $relatedPost = Post::factory()->create(['episode_id' => $episode->id]);
    $unrelatedPost = Post::factory()->create();

    $data = app(EpisodeViewModel::class)->data($episode);

    expect($data['episode']->is($episode))->toBeTrue()
        ->and($data['relatedPosts']->modelKeys())
        ->toContain($relatedPost->id)
        ->not->toContain($unrelatedPost->id)
        ->and($data)->not->toHaveKey('isPreview');
});

test('episode preview data marks the payload as a preview', function (): void {
    $episode = Episode::factory()->draft()->create();

    expect(app(EpisodeViewModel::class)->data($episode, preview: true))
        ->toHaveKey('isPreview', true);
});
