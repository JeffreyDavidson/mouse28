<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

class EditorialReadiness
{
    public static function label(Post|Guide|Episode $content): string
    {
        $count = count(self::issues($content));

        return $count === 0 ? 'Ready' : "{$count} missing";
    }

    public static function color(Post|Guide|Episode $content): string
    {
        return self::issues($content) === [] ? 'success' : 'warning';
    }

    public static function summary(Post|Guide|Episode $content): string
    {
        $issues = self::issues($content);

        return $issues === [] ? 'Ready to publish.' : implode(' · ', $issues);
    }

    /** @return list<string> */
    public static function issues(Post|Guide|Episode $content): array
    {
        return match (true) {
            $content instanceof Post => self::postIssues($content),
            $content instanceof Guide => self::guideIssues($content),
            $content instanceof Episode => self::episodeIssues($content),
        };
    }

    /** @return list<string> */
    private static function postIssues(Post $post): array
    {
        return array_values(array_filter([
            blank($post->excerpt) ? 'Add an excerpt' : null,
            blank($post->content) ? 'Add post content' : null,
            blank($post->category_id) ? 'Choose a category' : null,
            blank($post->featured_image_path) ? 'Add a cover image' : null,
            filled($post->last_reviewed_at) && blank($post->source_url) ? 'Add an official source' : null,
            filled($post->source_url) && blank($post->last_reviewed_at) ? 'Set the review date' : null,
            blank($post->seo?->title) ? 'Add an SEO title' : null,
            blank($post->seo?->description) ? 'Add an SEO description' : null,
            self::needsPublishDate($post) ? 'Set a publish date' : null,
        ]));
    }

    /** @return list<string> */
    private static function guideIssues(Guide $guide): array
    {
        return array_values(array_filter([
            blank($guide->excerpt) ? 'Add an excerpt' : null,
            blank($guide->content) ? 'Add guide content' : null,
            blank($guide->featured_image_path) ? 'Add a cover image' : null,
            blank($guide->source_url) ? 'Add an official source' : null,
            blank($guide->last_reviewed_at) ? 'Set the review date' : null,
            blank($guide->seo?->title) ? 'Add an SEO title' : null,
            blank($guide->seo?->description) ? 'Add an SEO description' : null,
            self::needsPublishDate($guide) ? 'Set a publish date' : null,
        ]));
    }

    /** @return list<string> */
    private static function episodeIssues(Episode $episode): array
    {
        return array_values(array_filter([
            blank($episode->description) ? 'Add a description' : null,
            $episode->transistorEmbedUrl() === null && blank($episode->youtube_url) ? 'Add a Transistor share link or a YouTube video' : null,
            blank($episode->show_notes) ? 'Add show notes' : null,
            blank($episode->featured_image_path) ? 'Add a cover image' : null,
            blank($episode->duration_seconds) ? 'Set the duration' : null,
            blank($episode->seo?->title) ? 'Add an SEO title' : null,
            blank($episode->seo?->description) ? 'Add an SEO description' : null,
            self::needsPublishDate($episode) ? 'Set a publish date' : null,
        ]));
    }

    /** Published or scheduled content that has lost its publish date is not live until one is set. */
    private static function needsPublishDate(Post|Guide|Episode $content): bool
    {
        return in_array($content->status, [PublishStatus::Published, PublishStatus::Scheduled], true)
            && blank($content->published_at);
    }
}
