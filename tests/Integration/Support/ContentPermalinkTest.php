<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\ContentPermalink;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('content locks its URL at first publication and retains that lock when rescheduled', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $this->freezeSecond();
    $content = $factory->draft()->createOne();
    expect(ContentPermalink::isLocked($content))->toBeFalse();

    $content->update(['is_published' => true, 'published_at' => now()]);
    $firstPublication = $content->slug_locked_at;
    $content->update(['published_at' => now()->addWeek()]);

    expect(ContentPermalink::isLocked($content))->toBeTrue()
        ->and($content->refresh()->slug_locked_at)->toEqual($firstPublication);
})->with([
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
    'episode' => fn () => Episode::factory(),
]);

test('a scheduled record remembers having gone live before its date is cleared', function (): void {
    $content = Post::factory()->scheduled()->create();
    expect(ContentPermalink::isLocked($content))->toBeFalse();
    $this->travel(2)->days();

    $content->update(['is_published' => false, 'published_at' => null]);

    expect(ContentPermalink::isLocked($content->refresh()))->toBeTrue();
});
