<?php

use App\Enums\PublishStatus;
use App\Models\Concerns\LocksSlugAfterPublication;
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

covers(LocksSlugAfterPublication::class);

pest()->use(RefreshDatabase::class);

dataset('slug locking content', [
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
    'episode' => fn () => Episode::factory(),
    'newsletter issue' => fn () => NewsletterIssue::factory(),
]);

test('a draft slug stays unlocked', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->draft()
        ->createOne();

    expect($content->isSlugLocked())->toBeFalse()
        ->and($content->getAttribute('slug_locked_at'))
        ->toBeNull();
})->with('slug locking content');

test('publishing locks the slug', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->draft()
        ->createOne();

    $content->publish();

    $content->refresh();
    expect($content->isSlugLocked())->toBeTrue()
        ->and($content->getAttribute('slug_locked_at'))
        ->not->toBeNull();
})->with('slug locking content');

test('scheduling locks the slug before the publish date arrives', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->draft()
        ->createOne(['published_at' => Date::now()->addDay()]);

    $content->publish();

    expect($content->isScheduled())->toBeTrue()
        ->and($content->isSlugLocked())
        ->toBeTrue();
})->with('slug locking content');

test('the slug stays locked after the content is unpublished', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->draft()
        ->createOne();
    $content->publish();

    $content->unpublish();

    $content->refresh();
    expect($content->publishStatus())->toBe(PublishStatus::Draft)
        ->and($content->isSlugLocked())
        ->toBeTrue()
        ->and($content->getAttribute('slug_locked_at'))
        ->not->toBeNull();
})->with('slug locking content');

test('content saved as published without the publish action is locked', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->createOne(['status' => PublishStatus::Published]);

    expect($content->getAttribute('slug_locked_at'))->not->toBeNull()
        ->and($content->isSlugLocked())
        ->toBeTrue();
})->with('slug locking content');

test('the lock keeps its first timestamp when content is republished', function (): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $post = Post::factory()
        ->draft()
        ->create();
    $post->publish();
    $lockedAt = $post->getAttribute('slug_locked_at');

    Date::setTestNow('2026-10-04 12:00:00');
    $post->unpublish();
    $post->publish();

    expect($post->refresh()
        ->getAttribute('slug_locked_at'))->toEqual($lockedAt);
});
