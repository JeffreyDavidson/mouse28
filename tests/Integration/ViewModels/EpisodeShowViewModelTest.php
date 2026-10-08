<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Post;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(EpisodeShowViewModel::class);

pest()->use(RefreshDatabase::class);

test('episode data includes related published posts', function (): void {
    $episode = Episode::factory()->create();
    $relatedPost = Post::factory()->create();
    $relatedDraft = Post::factory()
        ->draft()
        ->create();
    $unrelatedPost = Post::factory()->create();
    $episode->posts()
        ->attach([$relatedPost->id, $relatedDraft->id]);

    $data = app(EpisodeShowViewModel::class)->data($episode);

    expect($data['episode']->is($episode))->toBeTrue()
        ->and($data['relatedPosts']->modelKeys())
        ->toBe([$relatedPost->id])
        ->and($data)
        ->not->toHaveKey('isPreview');
});

test('episode data includes posts linked to other episodes too', function (): void {
    $episode = Episode::factory()->create();
    $sharedPost = Post::factory()->create();
    $sharedPost->episodes()
        ->attach([$episode->id, Episode::factory()
            ->create()
            ->id]);

    $data = app(EpisodeShowViewModel::class)->data($episode);

    expect($data['relatedPosts']->modelKeys())->toBe([$sharedPost->id]);
});

test('episode preview data marks the payload as a preview', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();

    expect(app(EpisodeShowViewModel::class)->data($episode, preview: true))
        ->toHaveKey('isPreview', true);
});

test('episode data loads the category of each related post', function (): void {
    $episode = Episode::factory()->create();
    $category = Category::factory()->create(['name' => 'Sample Topic']);
    $posts = Post::factory()
        ->for($category)
        ->count(2)
        ->create();
    $episode->posts()
        ->attach($posts);

    $data = app(EpisodeShowViewModel::class)->data($episode);

    expect($data['relatedPosts']->every(fn (Post $post): bool => $post->relationLoaded('category')))->toBeTrue()
        ->and($data['relatedPosts']->pluck('category_label')
            ->all())
        ->toBe(['Sample Topic', 'Sample Topic'])
        ->and($data['relatedPosts']->first()
            ?->getAttributes())
        ->toHaveKey('category_id')
        ->not->toHaveKey('category');
});

test('episode related posts break publish-time ties by id so their order is stable on MySQL', function (): void {
    $episode = Episode::factory()->create();
    $episode->posts()
        ->attach(Post::factory()
            ->count(2)
            ->create());

    $orderings = publishTimeOrderings(fn () => app(EpisodeShowViewModel::class)->data($episode));

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
