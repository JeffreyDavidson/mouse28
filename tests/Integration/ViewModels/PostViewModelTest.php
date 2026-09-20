<?php

use App\Models\Episode;
use App\Models\Post;
use App\ViewModels\PostViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('post data includes recent posts and only published episode relationships', function (): void {
    $publishedEpisode = Episode::factory()->create();
    $post = Post::factory()->create(['episode_id' => $publishedEpisode->id]);
    $recentPost = Post::factory()->create(['category' => $post->category]);
    $unpublishedEpisode = Episode::factory()->draft()->create();

    $previewPost = Post::factory()->draft()->create(['episode_id' => $unpublishedEpisode->id]);
    $data = app(PostViewModel::class)->data($post);
    $previewData = app(PostViewModel::class)->data($previewPost, preview: true);

    expect($data['post']->relationLoaded('episode'))->toBeTrue()
        ->and($data['post']->episode?->is($publishedEpisode))->toBeTrue()
        ->and($data['recentPosts']->modelKeys())->toContain($recentPost->id)
        ->and($data)->not->toHaveKey('isPreview')
        ->and($previewData['post']->episode?->is($unpublishedEpisode))->toBeTrue()
        ->and($previewData)->toHaveKey('isPreview', true);
});
