<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PostCategory;
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
]);

test('sync refuses unpublished identity collisions without changing content', function (PostFactory|GuideFactory|EpisodeFactory $factory, string $state): void {
    // Arrange
    $record = $factory->createOne();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $record->update($state === 'draft'
        ? ['is_published' => false, 'title' => 'Local work']
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
