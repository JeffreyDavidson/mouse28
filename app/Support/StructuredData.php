<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Support\Str;

class StructuredData
{
    public static function forPost(Post $post): array
    {
        $modifiedAt = $post->updated_at;
        if ($post->last_reviewed_at?->gt($modifiedAt)) {
            $modifiedAt = $post->last_reviewed_at;
        }

        $article = [
            '@type' => 'BlogPosting',
            'headline' => $post->meta_title ?: $post->title,
            'description' => self::description($post->meta_description, $post->excerpt, $post->body),
            'mainEntityOfPage' => route('blog.show', $post),
            'datePublished' => $post->published_at->toAtomString(),
            'dateModified' => $modifiedAt->toAtomString(),
            'author' => self::person($post->author_name),
            'publisher' => self::publisher(),
            'articleSection' => $post->category_label,
        ];

        if ($image = $post->og_image_url ?: $post->cover_image_url) {
            $article['image'] = url($image);
        }

        if ($post->source_url) {
            $article['citation'] = $post->source_url;
        }

        return self::graph($article, 'BlogPosting', 'Blog', route('blog.index'), $post->title, route('blog.show', $post));
    }

    public static function forGuide(Guide $guide): array
    {
        $modifiedAt = $guide->updated_at;
        if ($guide->last_reviewed_at?->gt($modifiedAt)) {
            $modifiedAt = $guide->last_reviewed_at;
        }

        $article = [
            '@type' => 'Article',
            'headline' => $guide->meta_title ?: $guide->title,
            'description' => self::description($guide->meta_description, $guide->excerpt, $guide->body),
            'mainEntityOfPage' => route('guides.show', $guide),
            'datePublished' => $guide->published_at->toAtomString(),
            'dateModified' => $modifiedAt->toAtomString(),
            'author' => self::person($guide->author_name),
            'publisher' => self::publisher(),
            'articleSection' => $guide->category_label,
        ];

        if ($image = $guide->og_image_url ?: $guide->cover_image_url) {
            $article['image'] = url($image);
        }

        if ($guide->source_url) {
            $article['citation'] = $guide->source_url;
        }

        return self::graph($article, 'Article', 'Guides', route('guides.index'), $guide->title, route('guides.show', $guide));
    }

    public static function forEpisode(Episode $episode, ?Podcast $podcast = null): array
    {
        $podcastEpisode = [
            '@type' => 'PodcastEpisode',
            'name' => $episode->meta_title ?: $episode->title,
            'description' => self::description($episode->meta_description, $episode->description, $episode->show_notes),
            'url' => route('episodes.show', $episode),
            'datePublished' => $episode->published_at->toAtomString(),
            'episodeNumber' => $episode->episode_number,
            'partOfSeries' => [
                '@type' => 'PodcastSeries',
                'name' => $podcast?->name ?: 'Mouse28',
                'url' => route('episodes.index'),
            ],
        ];

        if ($episode->season_number) {
            $podcastEpisode['partOfSeason'] = [
                '@type' => 'PodcastSeason',
                'seasonNumber' => $episode->season_number,
            ];
        }

        if ($episode->duration_seconds) {
            $podcastEpisode['duration'] = self::duration($episode->duration_seconds);
        }

        if ($episode->audio_source_url) {
            $podcastEpisode['associatedMedia'] = [
                '@type' => 'MediaObject',
                'contentUrl' => $episode->audio_source_url,
            ];
        }

        $image = $episode->og_image_url ?: $episode->cover_image_url;
        if ($image) {
            $podcastEpisode['image'] = url($image);
        }

        return self::graph($podcastEpisode, 'PodcastEpisode', 'Podcast', route('episodes.index'), $episode->title, route('episodes.show', $episode));
    }

    private static function graph(array $content, string $type, string $sectionName, string $sectionUrl, string $title, string $url): array
    {
        $content['@id'] = $url.'#'.Str::kebab($type);

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $content,
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        self::breadcrumb(1, 'Home', route('home')),
                        self::breadcrumb(2, $sectionName, $sectionUrl),
                        self::breadcrumb(3, $title, $url),
                    ],
                ],
            ],
        ];
    }

    private static function breadcrumb(int $position, string $name, string $url): array
    {
        return [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url,
        ];
    }

    private static function description(?string $metaDescription, ?string $summary, ?string $body): string
    {
        return Str::limit($metaDescription ?: $summary ?: strip_tags($body ?? ''), 200);
    }

    private static function duration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return "PT{$hours}H{$minutes}M{$remainingSeconds}S";
    }

    private static function person(string $name): array
    {
        return [
            '@type' => 'Person',
            'name' => $name,
        ];
    }

    private static function publisher(): array
    {
        return [
            '@type' => 'Organization',
            'name' => 'Mouse28',
            'url' => route('home'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url('/images/logo.jpg'),
            ],
        ];
    }
}
