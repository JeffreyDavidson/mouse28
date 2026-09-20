<?php

namespace Database\Seeders;

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PostCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->episode('sample-episode-weekly-discussion', 'Sample Episode 01: Weekly Discussion', 9001, true, now()->subDay());
        $this->episode('sample-episode-planning-notes', 'Sample Episode 02: Planning Notes', 9002, false);
        $this->episode('sample-episode-upcoming-conversation', 'Sample Episode 03: Upcoming Conversation', 9003, true, now()->addDay());

        $this->post(
            'sample-post-planning-notes',
            'Sample Post 01: Planning Notes',
            PostCategory::ParkAccessibility,
            true,
            now()->subDay(),
        );
        $this->post('sample-post-draft-outline', 'Sample Post 02: Draft Outline', PostCategory::DisneyTips, false);
        $this->post('sample-post-scheduled-update', 'Sample Post 03: Scheduled Update', PostCategory::FamilyLife, true, now()->addDay());

        $this->guide('sample-guide-reference-information', 'Sample Guide 01: Reference Information', GuideCategory::Accessibility, true, now()->subDay());
        $this->guide('sample-guide-draft-outline', 'Sample Guide 02: Draft Outline', GuideCategory::ParkStrategy, false);
        $this->guide('sample-guide-scheduled-reference', 'Sample Guide 03: Scheduled Reference', GuideCategory::FoodReviews, true, now()->addDay());

    }

    private function episode(string $slug, string $title, int $episodeNumber, bool $isPublished, ?Carbon $publishedAt = null): Episode
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
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
            ],
        );
    }

    private function post(string $slug, string $title, PostCategory $category, bool $isPublished, ?Carbon $publishedAt = null): Post
    {
        return Post::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'excerpt' => 'This sample record exists to exercise the publishing workflow.',
                'body' => 'Synthetic development content for testing lists, detail pages, and filters.',
                'category' => $category,
                'author' => ContentAuthor::Both,
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
            ],
        );
    }

    private function guide(string $slug, string $title, GuideCategory $category, bool $isPublished, ?Carbon $publishedAt = null): Guide
    {
        return Guide::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'excerpt' => 'This sample record exists to exercise the publishing workflow.',
                'body' => 'Synthetic development content for testing lists, detail pages, and filters.',
                'category' => $category,
                'author' => ContentAuthor::Both,
                'source_url' => 'https://example.test/sample-source',
                'last_reviewed_at' => now()->toDateString(),
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
            ],
        );
    }
}
