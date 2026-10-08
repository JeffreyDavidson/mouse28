<?php

namespace Database\Seeders;

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $this->episode('sample-episode-weekly-discussion', 'Sample Episode 01: Weekly Discussion', 9001, PublishStatus::Published, now()->subDay());
        $this->episode('sample-episode-planning-notes', 'Sample Episode 02: Planning Notes', 9002, PublishStatus::Draft);
        $this->episode('sample-episode-upcoming-conversation', 'Sample Episode 03: Upcoming Conversation', 9003, PublishStatus::Scheduled, now()->addDay());

        $this->post(
            'sample-post-planning-notes',
            'Sample Post 01: Planning Notes',
            'park-accessibility',
            PublishStatus::Published,
            now()->subDay(),
        );
        $this->post('sample-post-draft-outline', 'Sample Post 02: Draft Outline', 'disney-tips', PublishStatus::Draft);
        $this->post('sample-post-scheduled-update', 'Sample Post 03: Scheduled Update', 'family-life', PublishStatus::Scheduled, now()->addDay());

        $this->guide('sample-guide-reference-information', 'Sample Guide 01: Reference Information', GuideCategory::Accessibility, PublishStatus::Published, now()->subDay());
        $this->guide('sample-guide-draft-outline', 'Sample Guide 02: Draft Outline', GuideCategory::ParkStrategy, PublishStatus::Draft);
        $this->guide('sample-guide-scheduled-reference', 'Sample Guide 03: Scheduled Reference', GuideCategory::FoodReviews, PublishStatus::Scheduled, now()->addDay());

        $this->issue('sample-issue-monthly-update', 'Sample Issue 01: Monthly Update', PublishStatus::Published, now()->subDay());
        $this->issue('sample-issue-draft-outline', 'Sample Issue 02: Draft Outline', PublishStatus::Draft);
        $this->issue('sample-issue-scheduled-note', 'Sample Issue 03: Scheduled Note', PublishStatus::Scheduled, now()->addDay());

        $this->subscriber('sample-active-reader@example.test', confirmed: true);
        $this->subscriber('sample-pending-reader@example.test', confirmed: false);
        $this->subscriber('sample-former-reader@example.test', confirmed: true, unsubscribed: true);
    }

    private function issue(string $slug, string $title, PublishStatus $status, ?Carbon $publishedAt = null): NewsletterIssue
    {
        return NewsletterIssue::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'excerpt' => 'This sample record exists to exercise the newsletter workflow.',
                'content' => 'Synthetic development content for testing the issue list, archive and emails.',
                'status' => $status,
                'published_at' => $publishedAt,
            ],
        );
    }

    private function subscriber(string $email, bool $confirmed, bool $unsubscribed = false): Subscriber
    {
        return Subscriber::query()->updateOrCreate(
            ['email' => $email],
            [
                'subscribed_at' => now(),
                'verified_at' => $confirmed ? now() : null,
                'unsubscribed_at' => $unsubscribed ? now() : null,
            ],
        );
    }

    private function episode(string $slug, string $title, int $episodeNumber, PublishStatus $status, ?Carbon $publishedAt = null): Episode
    {
        return Episode::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => 'This sample record exists to exercise the publishing workflow.',
                'show_notes' => 'Synthetic development notes for testing lists and detail pages.',
                'transcript' => 'This synthetic transcript is used for local development only.',
                'episode_number' => $episodeNumber,
                'season_number' => 99,
                'duration_seconds' => 1800,
                'status' => $status,
                'published_at' => $publishedAt,
            ],
        );
    }

    private function post(string $slug, string $title, string $categorySlug, PublishStatus $status, ?Carbon $publishedAt = null): Post
    {
        $post = Post::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'excerpt' => 'This sample record exists to exercise the publishing workflow.',
                'content' => 'Synthetic development content for testing lists, detail pages, and filters.',
                'category_id' => Category::query()
                    ->where('slug', $categorySlug)
                    ->value('id'),
                'status' => $status,
                'published_at' => $publishedAt,
            ],
        );
        $post->syncAuthors(User::authorIds());

        return $post;
    }

    private function guide(string $slug, string $title, GuideCategory $category, PublishStatus $status, ?Carbon $publishedAt = null): Guide
    {
        $guide = Guide::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'excerpt' => 'This sample record exists to exercise the publishing workflow.',
                'content' => 'Synthetic development content for testing lists, detail pages, and filters.',
                'category' => $category,
                'source_url' => 'https://example.test/sample-source',
                'last_reviewed_at' => now()->toDateString(),
                'status' => $status,
                'published_at' => $publishedAt,
            ],
        );
        $guide->syncAuthors(User::authorIds());

        return $guide;
    }
}
