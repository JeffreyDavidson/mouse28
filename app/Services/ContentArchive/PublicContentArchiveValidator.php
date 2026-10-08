<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use App\Enums\GuideCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Checks a public content archive before anything reads or imports it. `validate()`
 * checks the shape (version, record lists, unique identities, safe media paths, tags)
 * and maps older archive keys to current ones; `validateForPersistence()` applies the
 * field rules an import needs.
 *
 * @phpstan-type ValidatedArchive array{version: int, posts: list<array<string, mixed>>, guides: list<array<string, mixed>>, episodes: list<array<string, mixed>>, podcast: array<string, mixed>|null}
 */
final class PublicContentArchiveValidator
{
    /**
     * @param  array<string, mixed>  $archive
     * @return ValidatedArchive
     */
    public function validate(array $archive): array
    {
        if (($archive['version'] ?? null) !== PublicContentArchiveSchema::VERSION) {
            throw new InvalidArgumentException('The public content archive version is not supported.');
        }

        if (! array_key_exists('podcast', $archive) || (! is_array($archive['podcast']) && $archive['podcast'] !== null)) {
            throw new InvalidArgumentException('The public content archive contains invalid podcast metadata.');
        }

        // Archives exported before stored media paths existed carry the podcast cover as `cover_image`.
        $podcast = $archive['podcast'] === null ? null : $this->withLegacyCoverImage($archive['podcast'], 'cover_image_path');

        if (filled($podcast['cover_image_path'] ?? null)) {
            $this->validateMediaPath($podcast['cover_image_path']);
        }

        return [
            'version' => PublicContentArchiveSchema::VERSION,
            'posts' => $this->validateRecords($archive['posts'] ?? null, 'posts', [...PublicContentArchiveSchema::POST_FIELDS, ...PublicContentArchiveSchema::POST_CATEGORY_FIELDS, ...PublicContentArchiveSchema::AUTHOR_FIELDS, 'episode_slug', 'episode_slugs']),
            'guides' => $this->validateRecords($archive['guides'] ?? null, 'guides', [...PublicContentArchiveSchema::GUIDE_FIELDS, ...PublicContentArchiveSchema::AUTHOR_FIELDS]),
            'episodes' => $this->validateRecords($archive['episodes'] ?? null, 'episodes', PublicContentArchiveSchema::EPISODE_FIELDS),
            'podcast' => $podcast === null ? null : PublicContentArchiveSchema::only($podcast, PublicContentArchiveSchema::PODCAST_FIELDS),
        ];
    }

    /** @param ValidatedArchive $archive */
    public function validateForPersistence(array $archive): void
    {
        $rules = [];
        foreach (['posts', 'guides', 'episodes'] as $type) {
            $rules["{$type}.*.title"] = ['required', 'string', 'max:255'];
            $rules["{$type}.*.slug"] = ['required', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
            $rules["{$type}.*.published_at"] = ['required', 'date', 'before_or_equal:now'];
            $rules["{$type}.*.last_reviewed_at"] = ['nullable', 'date'];
            foreach (['excerpt', 'content', 'description', 'show_notes', 'transcript', 'meta_title', 'meta_description'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string'];
            }
            foreach (['featured_image_path'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string', 'max:255'];
            }
            foreach (['source_url', 'transistor_url', 'youtube_url'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string', 'url:http,https', 'max:255'];
            }
        }
        $rules['posts.*.episode_slug'] = ['nullable', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
        $rules['posts.*.episode_slugs'] = ['nullable', 'list'];
        $rules['posts.*.episode_slugs.*'] = ['required', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
        $rules['posts.*.content'] = ['present', 'string'];
        $rules['guides.*.content'] = ['present', 'string'];
        foreach (['posts', 'guides'] as $type) {
            $rules["{$type}.*.authors"] = ['nullable', 'list'];
            $rules["{$type}.*.authors.*"] = ['required', 'string', 'max:255'];
            $rules["{$type}.*.author"] = ['nullable', Rule::in(array_keys(PublicContentArchiveSchema::LEGACY_AUTHOR_NAMES))];
        }
        $rules['posts.*.category'] = ['nullable', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
        $rules['posts.*.category_name'] = ['nullable', 'string', 'max:255'];
        $rules['guides.*.category'] = ['required', Rule::enum(GuideCategory::class)];
        $rules['episodes.*.episode_number'] = ['required', 'integer', 'min:0', 'max:2147483647', 'distinct'];
        $rules['episodes.*.season_number'] = ['nullable', 'integer', 'min:0', 'max:4294967295'];
        $rules['episodes.*.duration_seconds'] = ['nullable', 'integer', 'min:0', 'max:2147483647'];
        $rules['podcast.name'] = ['required_with:podcast', 'string', 'max:255'];
        $rules['podcast.description'] = ['nullable', 'string'];
        $rules['podcast.cover_image_path'] = ['nullable', 'string', 'max:255'];
        foreach (['apple_url', 'spotify_url', 'youtube_url'] as $field) {
            $rules["podcast.{$field}"] = ['nullable', 'string', 'url:http,https', 'max:255'];
        }

        $validator = Validator::make($archive, $rules);
        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid public content archive: '.implode(' ', $validator->errors()
                ->all()));
        }
    }

    public function validateMediaPath(mixed $path): string
    {
        if (! is_string($path)
            || str_starts_with($path, '/')
            || str_contains($path, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $path) === 1
            || in_array('..', explode('/', $path), true)) {
            throw new InvalidArgumentException('The public content archive contains an unsafe media path.');
        }

        return $path;
    }

    /**
     * @param  list<string>  $fields
     * @return list<array<string, mixed>>
     */
    private function validateRecords(mixed $records, string $contentType, array $fields): array
    {
        if (! is_array($records)) {
            throw new InvalidArgumentException("The public content archive is missing {$contentType}.");
        }

        $validated = [];
        $slugs = [];
        $episodeNumbers = [];

        foreach ($records as $attributes) {
            if (! is_array($attributes) || ! is_string($attributes['slug'] ?? null) || blank($attributes['slug'])) {
                throw new InvalidArgumentException("The public content archive contains invalid {$contentType}.");
            }

            if (in_array($attributes['slug'], $slugs, true)) {
                throw new InvalidArgumentException("The public content archive contains duplicate {$contentType} slugs.");
            }
            $slugs[] = $attributes['slug'];

            // Archives exported before stored media paths existed carry the image as `cover_image`.
            $attributes = $this->withLegacyCoverImage($attributes, 'featured_image_path');

            foreach (['featured_image_path'] as $field) {
                if (filled($attributes[$field] ?? null)) {
                    $this->validateMediaPath($attributes[$field]);
                }
            }

            if (array_key_exists('tags', $attributes)
                && (! is_array($attributes['tags']) || ! array_is_list($attributes['tags'])
                    || array_any($attributes['tags'], fn (mixed $tag): bool => ! is_string($tag) || trim($tag) === '' || mb_strlen($tag) > 255))) {
                throw new InvalidArgumentException("The public content archive contains invalid {$contentType} tags.");
            }

            // Media inspection also accepts partial legacy records; persistence validates the full payload.
            if (array_key_exists('episode_number', $attributes)) {
                if (in_array($attributes['episode_number'], $episodeNumbers, true)) {
                    throw new InvalidArgumentException('The public content archive contains duplicate episode numbers.');
                }
                $episodeNumbers[] = $attributes['episode_number'];
            }

            // Archives exported before the content column existed carry post and guide text as `body`.
            if (! array_key_exists('content', $attributes) && array_key_exists('body', $attributes)) {
                $attributes['content'] = $attributes['body'];
            }

            $validated[] = PublicContentArchiveSchema::only($attributes, [...$fields, ...PublicContentArchiveSchema::SEO_FIELDS, 'tags']);
        }

        return $validated;
    }

    /**
     * Copies an older archive's `cover_image` into the stored media path column the
     * record now uses; the new key wins when an archive carries both.
     *
     * @param  array<array-key, mixed>  $attributes
     * @return array<array-key, mixed>
     */
    private function withLegacyCoverImage(array $attributes, string $column): array
    {
        if (! array_key_exists($column, $attributes) && array_key_exists('cover_image', $attributes)) {
            $attributes[$column] = $attributes['cover_image'];
        }

        return $attributes;
    }
}
