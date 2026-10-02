<?php

use App\Enums\PublishStatus;
use App\Models\Concerns\SyncsLegacyPublishedFlag;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(SyncsLegacyPublishedFlag::class);

pest()->use(RefreshDatabase::class);

dataset('legacy flag content', [
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
    'episode' => fn () => Episode::factory(),
    'newsletter issue' => fn () => NewsletterIssue::factory(),
]);

test('saving mirrors the publish status into the legacy published flag', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory, PublishStatus $status, bool $isPublished): void {
    $content = $factory->createOne(['status' => $status]);

    expect((bool) DB::table($content->getTable())->where('id', $content->getKey())->value('is_published'))->toBe($isPublished);
})->with('legacy flag content')->with([
    'draft' => [PublishStatus::Draft, false],
    'in review' => [PublishStatus::InReview, false],
    'published' => [PublishStatus::Published, true],
    'scheduled' => [PublishStatus::Scheduled, true],
]);

test('publishing and unpublishing keep the legacy published flag in step', function (PostFactory|GuideFactory|EpisodeFactory|NewsletterIssueFactory $factory): void {
    $content = $factory->draft()->createOne();
    $table = DB::table($content->getTable())->where('id', $content->getKey());

    $content->publish();
    $afterPublish = (bool) $table->value('is_published');
    $content->unpublish();
    $afterUnpublish = (bool) $table->value('is_published');

    expect($afterPublish)->toBeTrue()
        ->and($afterUnpublish)->toBeFalse();
})->with('legacy flag content');

test('a legacy published flag written directly is overwritten by the status on save', function (): void {
    $post = Post::factory()->draft()->create();

    $post->forceFill(['is_published' => true])->save();

    expect(DB::table('posts')->where('id', $post->id)->value('is_published'))->toBeFalsy();
});
