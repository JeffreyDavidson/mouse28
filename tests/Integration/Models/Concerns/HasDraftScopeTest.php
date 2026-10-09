<?php

use App\Enums\PublishStatus;
use App\Models\Concerns\HasDraftScope;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(HasDraftScope::class);

pest()->use(RefreshDatabase::class);

dataset('publishable content', [
    'posts' => [fn (): PostFactory => Post::factory()],
    'guides' => [fn (): GuideFactory => Guide::factory()],
    'episodes' => [fn (): EpisodeFactory => Episode::factory()],
    'newsletter issues' => [fn (): NewsletterIssueFactory => NewsletterIssue::factory()],
]);

test('the drafts scope finds draft and in-review records only', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    // Arrange
    $draft = $factory->draft()
        ->createOne();
    $inReview = $factory->draft()
        ->createOne(['status' => PublishStatus::InReview]);
    $factory->createOne();
    $factory->scheduled()
        ->createOne();

    // Act
    $ids = $draft::query()
        ->drafts()
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toEqualCanonicalizing([$draft->id, $inReview->id]);
})->with('publishable content');

test('the drafts scope reads its own table when joined to another publishable table', function (): void {
    // Arrange
    $draftPost = Post::factory()
        ->draft()
        ->createOne();
    $livePost = Post::factory()->createOne();
    $liveEpisode = Episode::factory()->createOne();
    $draftPost->episodes()
        ->attach($liveEpisode);
    $livePost->episodes()
        ->attach(Episode::factory()
            ->draft()
            ->createOne());

    // Act
    $ids = Post::query()
        ->join('episode_post', 'episode_post.post_id', '=', 'posts.id')
        ->join('episodes', 'episodes.id', '=', 'episode_post.episode_id')
        ->drafts()
        ->pluck('posts.id')
        ->all();

    // Assert
    expect($ids)->toBe([$draftPost->id]);
});
