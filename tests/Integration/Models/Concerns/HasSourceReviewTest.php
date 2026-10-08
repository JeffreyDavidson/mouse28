<?php

use App\Enums\SourceReviewStatus;
use App\Models\Concerns\HasSourceReview;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(HasSourceReview::class);

pest()->use(RefreshDatabase::class);

dataset('reviewed content', [
    'post' => [fn (): PostFactory => Post::factory()->state(['source_url' => 'https://example.test/source']), 'content.post_review_interval_days'],
    'guide' => [fn (): GuideFactory => Guide::factory(), 'content.guide_review_interval_days'],
]);

test('content is due for review once its last review is older than its own interval', function (PostFactory|GuideFactory $factory, string $intervalKey): void {
    // Arrange
    $this->freezeTime();
    config()->set('content.post_review_interval_days', 365);
    config()->set('content.guide_review_interval_days', 365);
    config()->set($intervalKey, 30);
    $current = $factory->createOne(['last_reviewed_at' => today()->subDays(30)]);
    $stale = $factory->createOne(['last_reviewed_at' => today()->subDays(31)]);
    $unreviewed = $factory->createOne(['last_reviewed_at' => null]);

    // Act
    $reviewDueIds = $current::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    // Assert
    expect($reviewDueIds)->toEqualCanonicalizing([$stale->id, $unreviewed->id])
        ->and($current->isReviewDue())
        ->toBeFalse()
        ->and($current->sourceReviewStatus())
        ->toBe(SourceReviewStatus::Current)
        ->and($stale->isReviewDue())
        ->toBeTrue()
        ->and($stale->sourceReviewStatus())
        ->toBe(SourceReviewStatus::ReviewDue)
        ->and($unreviewed->sourceReviewStatus())
        ->toBe(SourceReviewStatus::ReviewDue);
})->with('reviewed content');

test('posts track source review only when they cite an official source', function (): void {
    // Arrange
    $untracked = Post::factory()->createOne([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);

    // Act
    $reviewDueIds = Post::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    // Assert
    expect($reviewDueIds)->toBe([])
        ->and($untracked->isReviewDue())
        ->toBeFalse()
        ->and($untracked->sourceReviewStatus())
        ->toBe(SourceReviewStatus::NotTracked);
});

test('guides are always tracked for source review', function (): void {
    // Arrange
    $guide = Guide::factory()->createOne([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);

    // Act
    $reviewDueIds = Guide::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    // Assert
    expect($reviewDueIds)->toBe([$guide->id])
        ->and($guide->sourceReviewStatus())
        ->toBe(SourceReviewStatus::ReviewDue);
});
