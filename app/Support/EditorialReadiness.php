<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

class EditorialReadiness
{
    public static function status(Post|Guide|Episode $content): string
    {
        if (! $content->is_published) {
            return 'Draft';
        }

        if (! $content->published_at) {
            return 'Needs publish date';
        }

        return $content->published_at->isFuture() ? 'Scheduled' : 'Published';
    }

    public static function statusColor(Post|Guide|Episode $content): string
    {
        return match (self::status($content)) {
            'Published' => 'success',
            'Scheduled', 'Needs publish date' => 'warning',
            default => 'gray',
        };
    }

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
            blank($post->body) ? 'Add post content' : null,
            blank($post->cover_image) ? 'Add a cover image' : null,
            blank($post->meta_title) ? 'Add an SEO title' : null,
            blank($post->meta_description) ? 'Add an SEO description' : null,
            blank($post->published_at) ? 'Set a publish date' : null,
        ]));
    }

    /** @return list<string> */
    private static function guideIssues(Guide $guide): array
    {
        return array_values(array_filter([
            blank($guide->excerpt) ? 'Add an excerpt' : null,
            blank($guide->body) ? 'Add guide content' : null,
            blank($guide->cover_image) ? 'Add a cover image' : null,
            blank($guide->source_url) ? 'Add an official source' : null,
            blank($guide->last_reviewed_at) ? 'Set the review date' : null,
            blank($guide->meta_title) ? 'Add an SEO title' : null,
            blank($guide->meta_description) ? 'Add an SEO description' : null,
            blank($guide->published_at) ? 'Set a publish date' : null,
        ]));
    }

    /** @return list<string> */
    private static function episodeIssues(Episode $episode): array
    {
        return array_values(array_filter([
            blank($episode->description) ? 'Add a description' : null,
            blank($episode->show_notes) ? 'Add show notes' : null,
            blank($episode->transcript) ? 'Add a transcript' : null,
            blank($episode->audio_url) ? 'Add the audio URL' : null,
            blank($episode->cover_image) ? 'Add a cover image' : null,
            blank($episode->duration_seconds) ? 'Set the duration' : null,
            blank($episode->meta_title) ? 'Add an SEO title' : null,
            blank($episode->meta_description) ? 'Add an SEO description' : null,
            blank($episode->published_at) ? 'Set a publish date' : null,
        ]));
    }
}
