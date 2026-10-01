<?php

use App\Contracts\Publishable;
use App\Models\Concerns\HasPublication;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(HasPublication::class);

pest()->use(RefreshDatabase::class);

test('content is published only when live with a past publication date', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');

    $live = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()]);
    $scheduled = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()->addMinute()]);
    $draft = Post::factory()->create(['is_published' => false, 'published_at' => Date::now()->subDay()]);
    $undated = Post::factory()->make(['is_published' => true, 'published_at' => null]);

    expect($live->isPublished())->toBeTrue()
        ->and($scheduled->isPublished())->toBeFalse()
        ->and($draft->isPublished())->toBeFalse()
        ->and($undated->isPublished())->toBeFalse()
        ->and(Post::published()->pluck('id')->all())->toBe([$live->id]);
});

test('content is scheduled only when published with a future publication date', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');

    $live = Post::factory()->make(['is_published' => true, 'published_at' => Date::now()]);
    $scheduled = Post::factory()->make(['is_published' => true, 'published_at' => Date::now()->addMinute()]);
    $unpublishedFuture = Post::factory()->make(['is_published' => false, 'published_at' => Date::now()->addDay()]);
    $undated = Post::factory()->make(['is_published' => true, 'published_at' => null]);

    expect($scheduled->isScheduled())->toBeTrue()
        ->and($live->isScheduled())->toBeFalse()
        ->and($unpublishedFuture->isScheduled())->toBeFalse()
        ->and($undated->isScheduled())->toBeFalse();
});

test('publishing keeps an existing publication date and otherwise uses now', function (?string $publishedAt, string $expected): void {
    Date::setTestNow('2026-09-25 12:00:00');
    $post = Post::factory()->draft()->create(['published_at' => $publishedAt]);

    $post->publish();

    expect($post->refresh()->is_published)->toBeTrue()
        ->and($post->published_at?->toDateTimeString())->toBe($expected);
})->with([
    'undated' => [null, '2026-09-25 12:00:00'],
    'future date' => ['2026-10-01 09:00:00', '2026-10-01 09:00:00'],
    'past date' => ['2026-09-01 09:00:00', '2026-09-01 09:00:00'],
]);

test('unpublishing returns content to draft while keeping its date and permalink', function (): void {
    $post = Post::factory()->create(['slug' => 'permanent-url', 'published_at' => '2026-09-01 09:00:00']);

    $post->unpublish();

    expect($post->refresh()->is_published)->toBeFalse()
        ->and($post->published_at?->toDateTimeString())->toBe('2026-09-01 09:00:00')
        ->and($post->slug)->toBe('permanent-url')
        ->and($post->slug_locked_at)->not->toBeNull();
});

test('publication scopes separate drafts from scheduled content', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');

    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()->addDay()]);

    expect(Post::drafts()->pluck('id')->all())->toBe([$draft->id])
        ->and(Post::scheduled()->pluck('id')->all())->toBe([$scheduled->id]);
});

test('editorial content models share the publication rules', function (string $model): void {
    expect(class_uses_recursive($model))->toContain(HasPublication::class)
        ->and(class_implements($model))->toContain(Publishable::class);
})->with([
    'post' => [Post::class],
    'guide' => [Guide::class],
    'episode' => [Episode::class],
]);
