<?php

use App\Models\Concerns\ScopesNewestFirst;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(ScopesNewestFirst::class);

pest()->use(RefreshDatabase::class);

dataset('dated content', [
    'posts' => [fn (): PostFactory => Post::factory()],
    'guides' => [fn (): GuideFactory => Guide::factory()],
    'episodes' => [fn (): EpisodeFactory => Episode::factory()],
    'newsletter issues' => [fn (): NewsletterIssueFactory => NewsletterIssue::factory()],
]);

test('newest first orders by publication date and breaks ties by the newest id', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    // Arrange
    $this->freezeTime();
    $oldest = $factory->createOne(['published_at' => now()->subDays(3)]);
    $firstTied = $factory->createOne(['published_at' => now()->subDay()]);
    $secondTied = $factory->createOne(['published_at' => now()->subDay()]);
    $newest = $factory->createOne(['published_at' => now()->subHour()]);

    // Act
    $ids = $oldest::query()
        ->newestFirst()
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe([$newest->id, $secondTied->id, $firstTied->id, $oldest->id]);
})->with('dated content');

test('newest first reads its own table when joined to another dated table', function (): void {
    // Arrange
    $this->freezeTime();
    $olderPost = Post::factory()->createOne(['published_at' => now()->subDays(2)]);
    $newerPost = Post::factory()->createOne(['published_at' => now()->subDay()]);
    $olderPost->episodes()
        ->attach(Episode::factory()->createOne(['published_at' => now()]));
    $newerPost->episodes()
        ->attach(Episode::factory()->createOne(['published_at' => now()->subDays(5)]));

    // Act
    $ids = Post::query()
        ->join('episode_post', 'episode_post.post_id', '=', 'posts.id')
        ->join('episodes', 'episodes.id', '=', 'episode_post.episode_id')
        ->newestFirst()
        ->pluck('posts.id')
        ->all();

    // Assert
    expect($ids)->toBe([$newerPost->id, $olderPost->id]);
});
