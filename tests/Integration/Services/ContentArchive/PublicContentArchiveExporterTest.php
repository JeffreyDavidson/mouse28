<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use App\Services\ContentArchive\PublicContentArchiveExporter;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(PublicContentArchiveExporter::class);

pest()->use(RefreshDatabase::class);

test('archive export writes each post category slug and name', function (): void {
    Post::factory()->inCategory('disney-tips')->create(['published_at' => now()->subDays(2)]);
    Post::factory()->for(Category::factory()->create(['name' => 'Water Parks', 'slug' => 'water-parks']))->create(['published_at' => now()->subDay()]);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(array_map(fn (array $post): array => [$post['category'], $post['category_name']], $archive['posts']))->toBe([
        ['disney-tips', 'Disney Tips'],
        ['water-parks', 'Water Parks'],
    ]);
});

test('archive export carries no category for an uncategorized post', function (): void {
    Post::factory()->create(['category_id' => null]);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect($archive['posts'][0]['category'])->toBeNull()
        ->and($archive['posts'][0]['category_name'])->toBeNull();
});

test('export includes only live content by its publish status', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $factory->createOne(['slug' => 'live-content']);
    $factory->draft()->createOne();
    $factory->createOne(['status' => PublishStatus::InReview]);
    $factory->scheduled()->createOne();

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(array_column([...$archive['posts'], ...$archive['guides'], ...$archive['episodes']], 'slug'))->toBe(['live-content']);
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
]);

test('archive export writes the written content under the content key', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne(['content' => "## Arrival\n\nPlan a flexible arrival."]);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(firstArchivedRecord($archive, $type))->toHaveKey('content', "## Arrival\n\nPlan a flexible arrival.")
        ->not->toHaveKey('body');
})->with('archived written content');

test('archive export lists the published related episodes of each post in episode number order', function (): void {
    $post = Post::factory()->create();
    $post->episodes()->attach([
        Episode::factory()->create(['slug' => 'later-episode', 'episode_number' => 12])->id,
        Episode::factory()->create(['slug' => 'earlier-episode', 'episode_number' => 3])->id,
        Episode::factory()->draft()->create(['slug' => 'draft-episode', 'episode_number' => 1])->id,
    ]);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(firstArchivedRecord($archive, 'posts'))->toHaveKey('episode_slugs', ['earlier-episode', 'later-episode'])
        ->not->toHaveKey('episode_slug');
});

test('archive export writes the author names of each post and guide in byline order', function (PostFactory|GuideFactory $factory, string $type): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $factory->withAuthors($cassie, $jeffrey)->createOne();

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(firstArchivedRecord($archive, $type))->toHaveKey('authors', ['Cassie Davidson', 'Jeffrey Davidson'])
        ->not->toHaveKey('author');
})->with('archived written content');

test('archive export writes an empty author list for content without authors', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne();

    expect(firstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), $type))->toHaveKey('authors', []);
})->with('archived written content');

test('archive export leaves out the private review fields of each post', function (): void {
    Post::factory()->create([
        'review_notes' => 'Private editorial note',
        'reviewed_by' => User::factory()->create()->id,
        'reviewed_at' => now(),
    ]);

    $post = firstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts');

    expect($post)->not->toHaveKeys(['review_notes', 'reviewed_by', 'reviewed_at']);
});
