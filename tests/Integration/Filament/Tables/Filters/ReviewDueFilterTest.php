<?php

use App\Filament\Tables\Filters\ReviewDueFilter;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

covers(ReviewDueFilter::class);

dataset('reviewable models', [
    'posts' => [fn () => Post::factory()],
    'guides' => [fn () => Guide::factory()],
]);

test('the review due filter is named review_due', function (): void {
    expect(ReviewDueFilter::make()->getName())->toBe('review_due');
});

test('the review due filter lists published content whose source review is due', function (PostFactory|GuideFactory $factory): void {
    // Arrange
    $due = $factory->createOne(['source_url' => 'https://example.test/source', 'last_reviewed_at' => null]);
    $current = $factory->createOne(['source_url' => 'https://example.test/source', 'last_reviewed_at' => Date::today()]);

    // Act
    $ids = ReviewDueFilter::make()
        ->apply($due::query(), ['isActive' => true])
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe([$due->id])
        ->and($ids)
        ->not->toContain($current->id);
})->with('reviewable models');

test('the review due filter leaves out content that is not published', function (PostFactory|GuideFactory $factory): void {
    // Arrange
    $published = $factory->createOne(['source_url' => 'https://example.test/source', 'last_reviewed_at' => null]);
    $draft = $factory->draft()
        ->createOne(['source_url' => 'https://example.test/source', 'last_reviewed_at' => null]);

    // Act
    $ids = ReviewDueFilter::make()
        ->apply($published::query(), ['isActive' => true])
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe([$published->id])
        ->and($ids)
        ->not->toContain($draft->id);
})->with('reviewable models');

test('the review due filter leaves out posts without an official source', function (): void {
    // Arrange
    $withoutSource = Post::factory()->createOne(['source_url' => null, 'last_reviewed_at' => null]);

    // Act
    $ids = ReviewDueFilter::make()
        ->apply(Post::query(), ['isActive' => true])
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->not->toContain($withoutSource->id);
});
