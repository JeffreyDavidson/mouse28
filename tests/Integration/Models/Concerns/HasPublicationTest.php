<?php

use App\Models\Concerns\HasPublication;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(HasPublication::class);

pest()->use(RefreshDatabase::class);

test('content is live only when published with a past publication date', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');

    $live = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()]);
    $scheduled = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()->addMinute()]);
    $draft = Post::factory()->create(['is_published' => false, 'published_at' => Date::now()->subDay()]);
    $undated = Post::factory()->make(['is_published' => true, 'published_at' => null]);

    expect($live->isLive())->toBeTrue()
        ->and($scheduled->isLive())->toBeFalse()
        ->and($draft->isLive())->toBeFalse()
        ->and($undated->isLive())->toBeFalse()
        ->and(Post::published()->pluck('id')->all())->toBe([$live->id]);
});

test('publication scopes separate drafts from scheduled content', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');

    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->create(['is_published' => true, 'published_at' => Date::now()->addDay()]);

    expect(Post::drafts()->pluck('id')->all())->toBe([$draft->id])
        ->and(Post::scheduled()->pluck('id')->all())->toBe([$scheduled->id]);
});

test('stored image paths resolve to public storage URLs', function (): void {
    $post = new Post(['cover_image' => 'covers/a.jpg', 'og_image' => '']);

    expect($post->cover_image_url)->toBe('/storage/covers/a.jpg')
        ->and($post->og_image_url)->toBeNull();
});

test('editorial content models share the publication rules', function (string $model): void {
    expect(class_uses_recursive($model))->toContain(HasPublication::class);
})->with([
    'post' => [Post::class],
    'guide' => [Guide::class],
    'episode' => [Episode::class],
]);
