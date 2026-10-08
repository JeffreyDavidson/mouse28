<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

/**
 * The public content archive format shared by the exporter, importer and relations.
 * The class and `only()` follow The Laravel Architect; the fields are mouse28's own
 * format (guides, author names, related episode slugs and the original SEO keys).
 */
final class PublicContentArchiveSchema
{
    public const int VERSION = 1;

    public const array EPISODE_FIELDS = [
        'title',
        'slug',
        'description',
        'show_notes',
        'transcript',
        'episode_number',
        'season_number',
        'transistor_url',
        'youtube_url',
        'duration_seconds',
        'featured_image_path',
        'published_at',
    ];

    public const array POST_FIELDS = [
        'title',
        'slug',
        'excerpt',
        'content',
        'source_url',
        'last_reviewed_at',
        'featured_image_path',
        'published_at',
    ];

    /** The archive keeps its original SEO keys; they map to the saved SEO row's title and description. */
    public const array SEO_FIELDS = ['meta_title', 'meta_description'];

    /** A post's category travels by slug and name, so an import can create one the local site lacks. */
    public const array POST_CATEGORY_FIELDS = ['category', 'category_name'];

    /**
     * Authors travel by name (`authors`, in byline order). Archives exported before
     * authors became users carry a single legacy `author` value instead.
     */
    public const array AUTHOR_FIELDS = ['authors', 'author'];

    /**
     * The author names (in byline order) credited by each legacy `author` value.
     *
     * @var array<string, list<string>>
     */
    public const array LEGACY_AUTHOR_NAMES = [
        'jeffrey' => ['Jeffrey Davidson'],
        'cassie' => ['Cassie Davidson'],
        'both' => ['Jeffrey Davidson', 'Cassie Davidson'],
    ];

    public const array GUIDE_FIELDS = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'featured_image_path',
        'source_url',
        'last_reviewed_at',
        'published_at',
    ];

    public const array PODCAST_FIELDS = [
        'name',
        'description',
        'cover_image_path',
        'apple_url',
        'spotify_url',
        'youtube_url',
    ];

    /**
     * Select the given fields that are present in the attributes, in field order.
     *
     * @param  array<array-key, mixed>  $attributes
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    public static function only(array $attributes, array $fields): array
    {
        $selected = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $attributes)) {
                $selected[$field] = $attributes[$field];
            }
        }

        return $selected;
    }
}
