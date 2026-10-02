<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PostCategory;
use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\PublicContentArchive;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('content tags round trip and replace stale tags while legacy archives preserve them', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();
    foreach ([$post, $guide, $episode] as $record) {
        $record->syncTagsWithType(['Accessibility', 'Family'], 'content');
    }
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    foreach ([$post, $guide, $episode] as $record) {
        $record->syncTagsWithType(['Stale'], 'content');
    }

    $service->import($archive);

    foreach ([$post, $guide, $episode] as $record) {
        expect($record->refresh()->tagsWithType('content')->pluck('name')->sort()->values()->all())
            ->toBe(['Accessibility', 'Family']);
    }
    unset($archive['posts'][0]['tags']);
    $archive['guides'][0]['tags'] = [];

    $service->import($archive);

    expect($post->refresh()->tagsWithType('content'))->toHaveCount(2)
        ->and($guide->refresh()->tagsWithType('content'))->toBeEmpty();
});

test('invalid imported attributes leave all existing records unchanged', function (string $field, mixed $value): void {
    $post = Post::factory()->create(['title' => 'Original']);
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive['posts'][0]['title'] = 'Changed';
    $archive['posts'][0][$field] = $value;

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and($post->refresh()->title)->toBe('Original');
})->with([
    'unsafe media' => ['cover_image', '../private.jpg'],
    'invalid date' => ['published_at', 'not a date'],
    'invalid slug' => ['slug', 'folder/story'],
    'invalid tags' => ['tags', [42]],
    'invalid URL' => ['source_url', 'javascript:alert(1)'],
    'invalid media type' => ['cover_image', []],
    'invalid relation type' => ['episode_slug', []],
    'null content' => ['content', null],
]);

test('sync refuses unpublished identity collisions without changing content', function (PostFactory|GuideFactory|EpisodeFactory $factory, string $state): void {
    // Arrange
    $record = $factory->createOne();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $record->update($state === 'draft'
        ? ['status' => PublishStatus::Draft, 'title' => 'Local work']
        : ['published_at' => now()->addWeek(), 'title' => 'Local work']);

    $exception = null;

    // Act
    try {
        $service->sync($archive);
    } catch (InvalidArgumentException $caught) {
        $exception = $caught;
    }

    // Assert
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($record->refresh()->title)->toBe('Local work');
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
])->with(['draft', 'scheduled']);

test('public archives retain string values and restore enum backed content', function (): void {
    $post = Post::factory()->create(['author' => ContentAuthor::Cassie, 'category' => PostCategory::DisneyTips]);
    $guide = Guide::factory()->create(['author' => ContentAuthor::Both, 'category' => GuideCategory::Accessibility]);
    $service = app(PublicContentArchive::class);

    $archive = $service->export();

    expect($archive['posts'][0]['author'])->toBe('cassie')
        ->and($archive['posts'][0]['category'])->toBe('disney-tips')
        ->and($archive['guides'][0]['author'])->toBe('both')
        ->and($archive['guides'][0]['category'])->toBe('accessibility');

    $post->delete();
    $guide->delete();

    $serializedArchive = json_encode($archive, JSON_THROW_ON_ERROR);
    $decodedArchive = json_decode($serializedArchive, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decodedArchive)) {
        throw new UnexpectedValueException('The serialized archive must decode to an array.');
    }

    $importArchive = [];
    foreach ($decodedArchive as $key => $value) {
        if (! is_string($key)) {
            throw new UnexpectedValueException('The decoded archive keys must be strings.');
        }

        $importArchive[$key] = $value;
    }

    $service->import($importArchive);
    $post->refresh();
    $guide->refresh();

    expect($post->trashed())->toBeFalse()
        ->and($post->author)->toBe(ContentAuthor::Cassie)
        ->and($post->category)->toBe(PostCategory::DisneyTips)
        ->and($guide->trashed())->toBeFalse()
        ->and($guide->author)->toBe(ContentAuthor::Both)
        ->and($guide->category)->toBe(GuideCategory::Accessibility);
});

test('archive validation rejects a non-string slug before importing any records', function (): void {
    $post = Post::factory()->create(['title' => 'Original title']);
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive['posts'][0]['title'] = 'Changed by import';
    $archive['posts'][0]['slug'] = ['invalid-slug'];

    expect(fn () => $service->import($archive))
        ->toThrow(InvalidArgumentException::class, 'invalid posts');

    $post->refresh();

    expect($post->title)->toBe('Original title');
});

test('invalid archive enum values roll back earlier imported records', function (): void {
    $first = Post::factory()->create(['title' => 'Original first title', 'published_at' => now()->subDays(2)]);
    Post::factory()->create();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive['posts'][0]['title'] = 'Changed by import';
    $archive['posts'][1]['category'] = 'not-a-category';

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class);

    $first->refresh();

    expect($first->title)->toBe('Original first title');
});

test('archive validation requires guide enum values', function (string $field): void {
    Guide::factory()->create();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive['guides'][0][$field] = null;

    expect(fn () => $service->import($archive))
        ->toThrow(InvalidArgumentException::class, "{$field} field is required");
})->with([
    'author' => 'author',
    'category' => 'category',
]);

test('export includes only live content by its publish status', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $factory->createOne(['slug' => 'live-content']);
    $factory->draft()->createOne();
    $factory->createOne(['status' => PublishStatus::InReview]);
    $factory->scheduled()->createOne();

    $archive = app(PublicContentArchive::class)->export();

    expect(array_column([...$archive['posts'], ...$archive['guides'], ...$archive['episodes']], 'slug'))->toBe(['live-content']);
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
]);

test('archives without a status field import as published content', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $record = $factory->createOne();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $record->forceDelete();

    $service->import($archive);

    $imported = $record::query()->sole();
    expect([...$archive['posts'], ...$archive['guides'], ...$archive['episodes']])->each->not->toHaveKeys(['status', 'is_published'])
        ->and($imported->publishStatus())->toBe(PublishStatus::Published)
        ->and($imported->isPublished())->toBeTrue()
        ->and($imported->getAttribute('is_published'))->toBeTruthy();
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
]);

/**
 * @param  array<string, mixed>  $archive
 * @return array<array-key, mixed>
 */
function firstArchivedRecord(array $archive, string $type): array
{
    $records = $archive[$type] ?? null;

    if (! is_array($records) || ! is_array($records[0] ?? null)) {
        throw new UnexpectedValueException("The archive has no {$type}.");
    }

    return $records[0];
}

/**
 * Returns the archive with its first record of a type changed, optionally dropping keys.
 *
 * @param  array<string, mixed>  $archive
 * @param  array<string, string>  $changes
 * @param  list<string>  $without
 * @return array<string, mixed>
 */
function withFirstArchivedRecord(array $archive, string $type, array $changes, array $without = []): array
{
    $record = firstArchivedRecord($archive, $type);
    foreach ($without as $key) {
        unset($record[$key]);
    }

    return [...$archive, $type => [[...$record, ...$changes]]];
}

dataset('archived written content', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

test('archive export writes the written content under the content key', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne(['content' => "## Arrival\n\nPlan a flexible arrival."]);

    $archive = app(PublicContentArchive::class)->export();

    expect(firstArchivedRecord($archive, $type))->toHaveKey('content', "## Arrival\n\nPlan a flexible arrival.")
        ->not->toHaveKey('body');
})->with('archived written content');

test('archive import restores the written content from either archive format', function (PostFactory|GuideFactory $factory, string $type, string $key): void {
    $record = $factory->createOne(['content' => 'Exported content.']);
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive = withFirstArchivedRecord($archive, $type, [$key => "## Café ✨\n\nImported content."], without: ['content']);
    $record->forceDelete();

    $service->import($archive);

    $imported = $record::query()->sole();
    expect($imported->getAttribute('content'))->toBe("## Café ✨\n\nImported content.")
        ->and($imported->getAttribute('body'))->toBe("## Café ✨\n\nImported content.");
})->with('archived written content')->with([
    'current content key' => ['content'],
    'older body key' => ['body'],
]);

test('archive import prefers the content key when an archive carries both keys', function (PostFactory|GuideFactory $factory, string $type): void {
    $record = $factory->createOne();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive = withFirstArchivedRecord($archive, $type, ['content' => 'Current content.', 'body' => 'Older body.']);
    $record->forceDelete();

    $service->import($archive);

    expect($record::query()->sole()->getAttribute('content'))->toBe('Current content.');
})->with('archived written content');

test('archive import rejects a record without any written content', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne(['title' => 'Original']);
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive = withFirstArchivedRecord($archive, $type, ['title' => 'Changed'], without: ['content']);

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and($factory->newModel()->newQuery()->sole()->getAttribute('title'))->toBe('Original');
})->with('archived written content');
