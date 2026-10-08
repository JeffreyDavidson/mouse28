<?php

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(HasPublishingStatus::class);

pest()->use(RefreshDatabase::class);

dataset('publishable content', [
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
    'episode' => fn () => Episode::factory(),
    'newsletter issue' => fn () => NewsletterIssue::factory(),
]);

test('editorial content models share the publishing status rules', function (string $model): void {
    expect(class_uses_recursive($model))->toContain(HasPublishingStatus::class)
        ->and(class_implements($model))
        ->toContain(Publishable::class);
})->with([
    'post' => [Post::class],
    'guide' => [Guide::class],
    'episode' => [Episode::class],
    'newsletter issue' => [NewsletterIssue::class],
]);

test('model checks and database scopes agree on what is live and scheduled', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, PublishStatus $status, ?int $seconds, bool $live, bool $scheduled): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $content = $factory->createOne([
        'status' => $status,
        'published_at' => $seconds === null ? null : Date::now()->addSeconds($seconds),
    ]);
    $model = $content::class;

    expect($content->isPublished())->toBe($live)
        ->and($content->isScheduled())
        ->toBe($scheduled)
        ->and($model::query()->published()
            ->whereKey($content->getKey())
            ->exists())
        ->toBe($live)
        ->and($model::query()->unpublished()
            ->whereKey($content->getKey())
            ->exists())
        ->toBe(! $live)
        ->and($model::query()->scheduled()
            ->whereKey($content->getKey())
            ->exists())
        ->toBe($scheduled);
})->with('publishable content')
    ->with([
        'published in the past' => [PublishStatus::Published, -1, true, false],
        'published exactly now' => [PublishStatus::Published, 0, true, false],
        'published with a future date' => [PublishStatus::Published, 1, false, true],
        'published without a date' => [PublishStatus::Published, null, false, false],
        'scheduled and now due' => [PublishStatus::Scheduled, -1, true, false],
        'scheduled for the future' => [PublishStatus::Scheduled, 1, false, true],
        'draft with a past date' => [PublishStatus::Draft, -1, false, false],
        'draft with a future date' => [PublishStatus::Draft, 1, false, false],
        'in review with a past date' => [PublishStatus::InReview, -1, false, false],
    ]);

test('publishing keeps an existing date, otherwise uses now, and schedules future dates', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, ?string $publishedAt, string $expectedDate, PublishStatus $expectedStatus): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $content = $factory->draft()
        ->createOne(['published_at' => $publishedAt]);

    $content->publish();

    $content->refresh();
    expect($content->publishStatus())->toBe($expectedStatus)
        ->and($content->publishedAt()
            ?->toDateTimeString())
        ->toBe($expectedDate);
})->with('publishable content')
    ->with([
        'undated' => [null, '2026-10-02 12:00:00', PublishStatus::Published],
        'past date' => ['2026-09-01 09:00:00', '2026-09-01 09:00:00', PublishStatus::Published],
        'future date' => ['2026-10-09 09:00:00', '2026-10-09 09:00:00', PublishStatus::Scheduled],
    ]);

test('unpublishing returns live or scheduled content to draft and keeps its date and slug', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, string $publishedAt): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $content = $factory->draft()
        ->createOne(['slug' => 'permanent-url', 'published_at' => $publishedAt]);
    $content->publish();

    $content->unpublish();

    $content->refresh();
    expect($content->publishStatus())->toBe(PublishStatus::Draft)
        ->and($content->isPublished())
        ->toBeFalse()
        ->and($content->isScheduled())
        ->toBeFalse()
        ->and($content->publishedAt()
            ?->toDateTimeString())
        ->toBe($publishedAt)
        ->and($content->getAttribute('slug'))
        ->toBe('permanent-url');
})->with('publishable content')
    ->with([
        'published' => ['2026-09-01 09:00:00'],
        'scheduled' => ['2026-10-09 09:00:00'],
    ]);
