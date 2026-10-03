<?php

use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use App\ViewModels\PostShowViewModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(PostShowViewModel::class);

pest()->use(RefreshDatabase::class);

/**
 * Creates a published post related to a published, a draft, a scheduled and
 * a trashed episode, in that order of creation.
 *
 * @return array{0: Post, 1: array{published: Episode, draft: Episode, scheduled: Episode, trashed: Episode}}
 */
function postWithEveryKindOfRelatedEpisode(): array
{
    $episodes = [
        'published' => Episode::factory()->create(['episode_number' => 30]),
        'draft' => Episode::factory()->draft()->create(['episode_number' => 10]),
        'scheduled' => Episode::factory()->scheduled()->create(['episode_number' => 20]),
        'trashed' => Episode::factory()->create(['episode_number' => 5]),
    ];
    $post = Post::factory()->create();
    $post->episodes()->attach(array_map(fn (Episode $episode): int => $episode->id, $episodes));
    $episodes['trashed']->delete();

    return [$post, $episodes];
}

/** @return list<int> */
function relatedEpisodeIds(Post $post): array
{
    $episodes = $post->getRelation('episodes');

    if (! $episodes instanceof Collection) {
        throw new UnexpectedValueException('The related episodes were not loaded.');
    }

    return array_values($episodes->modelKeys());
}

test('post data includes recent posts', function (): void {
    $post = Post::factory()->create();
    $recentPost = Post::factory()->create(['category_id' => $post->category_id]);

    $data = app(PostShowViewModel::class)->data($post);

    expect($data['recentPosts']->modelKeys())->toContain($recentPost->id)
        ->and($data)->not->toHaveKey('isPreview');
});

test('post data lists every published related episode in episode number order', function (): void {
    $post = Post::factory()->create();
    $later = Episode::factory()->create(['episode_number' => 12]);
    $earlier = Episode::factory()->create(['episode_number' => 3]);
    $post->episodes()->attach([$later->id, $earlier->id]);

    $data = app(PostShowViewModel::class)->data($post);

    expect(relatedEpisodeIds($data['post']))->toBe([$earlier->id, $later->id]);
});

test('post data leaves out draft, scheduled and trashed related episodes', function (): void {
    [$post, $episodes] = postWithEveryKindOfRelatedEpisode();

    $data = app(PostShowViewModel::class)->data($post);

    expect(relatedEpisodeIds($data['post']))->toBe([$episodes['published']->id]);
});

test('post data for a preview lists every related episode that is not trashed', function (): void {
    [$post, $episodes] = postWithEveryKindOfRelatedEpisode();

    $previewData = app(PostShowViewModel::class)->data($post, preview: true);

    expect(relatedEpisodeIds($previewData['post']))->toBe([$episodes['draft']->id, $episodes['scheduled']->id, $episodes['published']->id])
        ->and($previewData)->toHaveKey('isPreview', true);
});

test('post data loads the category of the post', function (): void {
    $post = Post::factory()->create();

    $data = app(PostShowViewModel::class)->data($post);

    expect($data['post']->relationLoaded('category'))->toBeTrue()
        ->and($data['post']->category_label)->toBe($post->category?->name);
});

test('post data loads the authors of the post in byline order', function (): void {
    [$first, $second] = User::factory()->author()->count(2)->create()->all();
    $post = Post::factory()->withAuthors($second, $first)->create();

    $data = app(PostShowViewModel::class)->data($post);

    expect($data['post']->relationLoaded('authors'))->toBeTrue()
        ->and($data['post']->authors->modelKeys())->toBe([$second->id, $first->id]);
});
