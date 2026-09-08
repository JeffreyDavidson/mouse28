<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PostCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\PublicContentArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('sync refuses unpublished identity collisions without changing content', function (string $model, string $state): void {
    // Arrange
    $record = $model::factory()->create();
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
})->with([Post::class, Guide::class, Episode::class])->with(['draft', 'scheduled']);

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

    $service->import($decodedArchive);
    $post->refresh();
    $guide->refresh();

    expect($post->trashed())->toBeFalse()
        ->and($post->author)->toBe(ContentAuthor::Cassie)
        ->and($post->category)->toBe(PostCategory::DisneyTips)
        ->and($guide->trashed())->toBeFalse()
        ->and($guide->author)->toBe(ContentAuthor::Both)
        ->and($guide->category)->toBe(GuideCategory::Accessibility);
});

test('invalid archive enum values roll back earlier imported records', function (): void {
    $first = Post::factory()->create(['title' => 'Original first title', 'published_at' => now()->subDays(2)]);
    Post::factory()->create();
    $service = app(PublicContentArchive::class);
    $archive = $service->export();
    $archive['posts'][0]['title'] = 'Changed by import';
    $archive['posts'][1]['category'] = 'not-a-category';

    expect(fn () => $service->import($archive))->toThrow(ValueError::class);

    $first->refresh();

    expect($first->title)->toBe('Original first title');
});
