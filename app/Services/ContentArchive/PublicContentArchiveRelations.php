<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Writes an imported record's relations: category, authors, related episodes, SEO and
 * tags. A record that leaves a relation's keys out keeps its current value.
 */
final class PublicContentArchiveRelations
{
    /**
     * Finds the archived category by slug, creating it when the local site lacks it.
     * Older archives carry only the slug, so the name falls back to its headline.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function syncCategory(Post $post, array $attributes): void
    {
        if (! array_key_exists('category', $attributes)) {
            return;
        }

        $post->category()->associate($this->category($attributes));
    }

    /**
     * Credits the archived authors (existing author users matched by name, in archive
     * order; unknown names are ignored and no users are created). Older archives carry
     * a legacy `author` value instead; the `authors` list wins when both are present.
     * A record that carries neither keeps its current authors.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function syncAuthors(Post|Guide $record, array $attributes): void
    {
        if (! array_key_exists('authors', $attributes) && ! array_key_exists('author', $attributes)) {
            return;
        }

        $names = array_key_exists('authors', $attributes)
            ? (array) $attributes['authors']
            : $this->legacyAuthorNames($attributes['author']);
        $authors = User::authors()->whereIn('name', $names)->get()->unique('name')->keyBy('name');

        $record->syncAuthors(array_filter(array_map(
            fn (mixed $name): ?int => is_string($name) ? $authors->get($name)?->id : null,
            $names,
        )));
    }

    /**
     * Relates the post to the archived episodes that exist locally. Archives exported
     * before a post could relate to several episodes carry a single `episode_slug`;
     * the `episode_slugs` list wins when both are present.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function syncEpisodes(Post $post, array $attributes): void
    {
        $slugs = $attributes['episode_slugs'] ?? $attributes['episode_slug'] ?? [];

        $post->episodes()->sync(Episode::query()->whereIn('slug', is_array($slugs) ? $slugs : [$slugs])->pluck('id'));
    }

    /** @param array<string, mixed> $attributes */
    public function syncSeo(Episode|Post|Guide $record, array $attributes): void
    {
        if (! array_key_exists('meta_title', $attributes) && ! array_key_exists('meta_description', $attributes)) {
            return;
        }

        $record->seo()->updateOrCreate([], [
            'title' => $attributes['meta_title'] ?? null,
            'description' => $attributes['meta_description'] ?? null,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    public function syncTags(Episode|Post|Guide $record, array $attributes): void
    {
        if (is_array($attributes['tags'] ?? null)) {
            $record->syncTagsWithType($attributes['tags'], 'content');
        }
    }

    /** @param array<string, mixed> $attributes */
    private function category(array $attributes): ?Category
    {
        $slug = $attributes['category'];

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        $name = $attributes['category_name'] ?? null;

        return Category::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => is_string($name) && $name !== '' ? $name : Str::headline($slug)],
        );
    }

    /**
     * The author names a validated legacy `author` value credits, in byline order.
     *
     * @return list<string>
     */
    private function legacyAuthorNames(mixed $author): array
    {
        return is_string($author) ? PublicContentArchiveSchema::LEGACY_AUTHOR_NAMES[$author] : [];
    }
}
