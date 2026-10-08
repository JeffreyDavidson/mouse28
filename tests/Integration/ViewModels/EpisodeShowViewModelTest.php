<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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

test('episode data prepares the player, duration and layout for the page', function (): void {
    $playable = Episode::factory()->create([
        'transistor_url' => 'https://share.transistor.fm/s/abc123',
        'duration_seconds' => 107,
    ]);
    $sparse = Episode::factory()->create([
        'transistor_url' => null,
        'transcript' => null,
        'show_notes' => null,
        'duration_seconds' => null,
    ]);

    $playableData = app(EpisodeShowViewModel::class)->data($playable);
    $sparseData = app(EpisodeShowViewModel::class)->data($sparse);

    expect($playableData['embedUrl'])->toBe('https://share.transistor.fm/e/abc123')
        ->and($playableData['duration'])
        ->toBe('1:47')
        ->and($playableData['isSparseEpisode'])
        ->toBeFalse()
        ->and($sparseData['embedUrl'])
        ->toBeNull()
        ->and($sparseData['isSparseEpisode'])
        ->toBeTrue()
        ->and($sparseData['duration'])
        ->toBeEmpty();
});

test('episode data uses the episode artwork as the cover', function (): void {
    $episode = Episode::factory()->create(['featured_image_path' => 'episodes/cover.png']);

    $data = app(EpisodeShowViewModel::class)->data($episode);

    expect($data['coverImage'])->toBe(Storage::disk('public')->url('episodes/cover.png'));
});

test('episode data without artwork uses the bundled cover', function (): void {
    $data = app(EpisodeShowViewModel::class)->data(Episode::factory()->create());

    expect($data['coverImage'])->toBe('/images/podcast/mouse28-cover.webp')
        ->and($data['coverSrcset'])
        ->toBeNull();
});

test('episode data without artwork uses the uploaded podcast cover', function (): void {
    Podcast::factory()->create(['cover_image_path' => 'podcast/cover.png']);

    $data = app(EpisodeShowViewModel::class)->data(Episode::factory()->create());

    expect($data['coverImage'])->toBe('/storage/podcast/cover.png');
});

test('episode data lists the show links with the episode video in place of the channel', function (): void {
    Podcast::factory()->create([
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);
    $episode = Episode::factory()->create(['youtube_url' => 'https://youtube.com/watch?v=1']);

    $data = app(EpisodeShowViewModel::class)->data($episode);

    expect(array_column($data['listenLinks'], 'url', 'label'))->toBe([
        'Apple Podcasts' => 'https://podcasts.apple.com/show/mouse28',
        'YouTube' => 'https://youtube.com/watch?v=1',
        'RSS Feed' => config('podcast.rss_url'),
    ]);
});
