<?php

namespace App\Support;

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * @phpstan-type ValidatedArchive array{version: int, posts: list<array<string, mixed>>, guides: list<array<string, mixed>>, episodes: list<array<string, mixed>>, podcast: array<string, mixed>|null}
 */
class PublicContentArchive
{
    private const int VERSION = 1;

    private const array EPISODE_FIELDS = [
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

    private const array POST_FIELDS = [
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
    private const array SEO_FIELDS = ['meta_title', 'meta_description'];

    /** A post's category travels by slug and name, so an import can create one the local site lacks. */
    private const array POST_CATEGORY_FIELDS = ['category', 'category_name'];

    /**
     * Authors travel by name (`authors`, in byline order). Archives exported before
     * authors became users carry a single legacy `author` value instead.
     */
    private const array AUTHOR_FIELDS = ['authors', 'author'];

    /**
     * The author names (in byline order) credited by each legacy `author` value.
     *
     * @var array<string, list<string>>
     */
    private const array LEGACY_AUTHOR_NAMES = [
        'jeffrey' => ['Jeffrey Davidson'],
        'cassie' => ['Cassie Davidson'],
        'both' => ['Jeffrey Davidson', 'Cassie Davidson'],
    ];

    private const array GUIDE_FIELDS = [
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

    private const array PODCAST_FIELDS = [
        'name',
        'description',
        'cover_image_path',
        'apple_url',
        'spotify_url',
        'youtube_url',
        'instagram_url',
        'tiktok_url',
    ];

    /**
     * @return array{
     *     version: int,
     *     exported_at: string,
     *     episodes: list<array<string, mixed>>,
     *     posts: list<array<string, mixed>>,
     *     guides: list<array<string, mixed>>,
     *     podcast: array<string, mixed>|null
     * }
     */
    public function export(): array
    {
        $episodes = Episode::query()
            ->with(['tags', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...self::EPISODE_FIELDS])
            ->map(fn (Episode $episode): array => [...$this->attributes($episode, self::EPISODE_FIELDS), ...$this->seoAttributes($episode), 'tags' => $episode->tagsWithType('content')->pluck('name')->all()])
            ->values()
            ->all();

        $posts = Post::query()
            ->with(['tags', 'episodes', 'category', 'authors', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', 'category_id', ...self::POST_FIELDS])
            ->map(fn (Post $post): array => [
                ...$this->attributes($post, self::POST_FIELDS),
                ...$this->seoAttributes($post),
                'category' => $post->category?->slug,
                'category_name' => $post->category?->name,
                'authors' => $this->authorNames($post),
                'episode_slugs' => $this->publishedEpisodeSlugs($post),
                'tags' => $post->tagsWithType('content')->pluck('name')->all(),
            ])
            ->values()
            ->all();

        $guides = Guide::query()
            ->with(['tags', 'authors', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...self::GUIDE_FIELDS])
            ->map(fn (Guide $guide): array => [
                ...$this->attributes($guide, self::GUIDE_FIELDS),
                ...$this->seoAttributes($guide),
                'authors' => $this->authorNames($guide),
                'tags' => $guide->tagsWithType('content')->pluck('name')->all(),
            ])
            ->values()
            ->all();

        $podcast = Podcast::query()->first();

        return [
            'version' => self::VERSION,
            'exported_at' => Date::now()->toAtomString(),
            'episodes' => array_values($episodes),
            'posts' => array_values($posts),
            'guides' => array_values($guides),
            'podcast' => $podcast ? $this->attributes($podcast, self::PODCAST_FIELDS) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return array{posts: int, guides: int, episodes: int, podcast: int}
     */
    public function import(array $archive): array
    {
        return $this->persist($archive);
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return array{posts: int, guides: int, episodes: int, podcast: int, removed_posts: int, removed_guides: int, removed_episodes: int}
     */
    public function sync(array $archive): array
    {
        return $this->persist($archive, prunePublished: true);
    }

    /** @param array<string, mixed> $archive */
    public function assertSafeToSync(array $archive): void
    {
        $archive = $this->validate($archive);
        $this->validateForPersistence($archive);

        foreach (['posts' => Post::class, 'guides' => Guide::class, 'episodes' => Episode::class] as $type => $model) {
            $identity = $type === 'episodes' ? 'episode_number' : 'slug';
            $records = $model::query()
                ->whereIn($identity, array_column($archive[$type], $identity))
                ->get();

            foreach ($records as $record) {
                if (! $record->isPublished()) {
                    throw new InvalidArgumentException("Sync conflicts with local unpublished {$type}. Resolve the conflicting {$identity} before syncing.");
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return list<string>
     */
    public function mediaPaths(array $archive): array
    {
        $archive = $this->validate($archive);

        $paths = [];

        foreach ([$archive['episodes'], $archive['posts'], $archive['guides']] as $records) {
            foreach ($records as $attributes) {
                foreach (['featured_image_path'] as $field) {
                    if (filled($attributes[$field] ?? null)) {
                        $paths[] = $this->validateMediaPath($attributes[$field]);
                    }
                }
            }
        }

        $podcastCover = $archive['podcast']['cover_image_path'] ?? null;

        if (filled($podcastCover)) {
            $paths[] = $this->validateMediaPath($podcastCover);
        }

        return array_values(collect($paths)->unique()->sort()->all());
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return ($prunePublished is true ? array{posts: int, guides: int, episodes: int, podcast: int, removed_posts: int, removed_guides: int, removed_episodes: int} : array{posts: int, guides: int, episodes: int, podcast: int})
     */
    private function persist(array $archive, bool $prunePublished = false): array
    {
        $archive = $this->validate($archive);
        $this->validateForPersistence($archive);

        return DB::transaction(function () use ($archive, $prunePublished): array {
            if ($prunePublished) {
                $this->assertSafeToSync($archive);
            }

            foreach ($archive['episodes'] as $attributes) {
                $this->importEpisode($attributes);
            }

            foreach ($archive['posts'] as $attributes) {
                $this->importPost($attributes);
            }

            foreach ($archive['guides'] as $attributes) {
                $this->importGuide($attributes);
            }

            if (is_array($archive['podcast'])) {
                $podcast = Podcast::query()->first() ?? new Podcast;
                $podcast->fill($this->onlyAttributes($archive['podcast'], self::PODCAST_FIELDS));
                $podcast->save();
            }

            $counts = [
                'posts' => count($archive['posts']),
                'guides' => count($archive['guides']),
                'episodes' => count($archive['episodes']),
                'podcast' => is_array($archive['podcast']) ? 1 : 0,
            ];

            if (! $prunePublished) {
                return $counts;
            }

            return [
                ...$counts,
                'removed_posts' => $this->prunePublished(Post::query(), $archive['posts']),
                'removed_guides' => $this->prunePublished(Guide::query(), $archive['guides']),
                'removed_episodes' => $this->prunePublished(Episode::query(), $archive['episodes']),
            ];
        });
    }

    /** @param array<string, mixed> $attributes */
    private function importEpisode(array $attributes): void
    {
        $episode = Episode::withTrashed()->firstOrNew(['episode_number' => $attributes['episode_number']]);

        if ($episode->trashed()) {
            $episode->restore();
        }

        $episode->fill([
            ...$this->onlyAttributes($attributes, self::EPISODE_FIELDS),
            'status' => PublishStatus::Published,
        ]);
        $episode->save();
        $this->importSeo($episode, $attributes);
        if (is_array($attributes['tags'] ?? null)) {
            $episode->syncTagsWithType($attributes['tags'], 'content');
        }
    }

    /** @param array<string, mixed> $attributes */
    private function importPost(array $attributes): void
    {
        $post = Post::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($post->trashed()) {
            $post->restore();
        }

        $post->fill([
            ...$this->onlyAttributes($attributes, self::POST_FIELDS),
            'status' => PublishStatus::Published,
        ]);

        if (array_key_exists('category', $attributes)) {
            $post->category()->associate($this->importedCategory($attributes));
        }

        $post->save();
        $this->importSeo($post, $attributes);
        $this->importAuthors($post, $attributes);
        $post->episodes()->sync(Episode::query()->whereIn('slug', $this->relatedEpisodeSlugs($attributes))->pluck('id'));
        if (is_array($attributes['tags'] ?? null)) {
            $post->syncTagsWithType($attributes['tags'], 'content');
        }
    }

    /**
     * Finds the archived category by slug, creating it when the local site lacks it.
     * Older archives carry only the slug, so the name falls back to its headline.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function importedCategory(array $attributes): ?Category
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

    /** @return array<int, string> the names of the record's authors, in byline order */
    private function authorNames(Post|Guide $record): array
    {
        return $record->authors->map(fn (User $author): string => $author->name)->all();
    }

    /**
     * Credits the archived authors (existing author users matched by name, in archive
     * order; unknown names are ignored and no users are created). Older archives carry
     * a legacy `author` value instead; the `authors` list wins when both are present.
     * A record that carries neither keeps its current authors.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function importAuthors(Post|Guide $record, array $attributes): void
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
     * The author names a validated legacy `author` value credits, in byline order.
     *
     * @return list<string>
     */
    private function legacyAuthorNames(mixed $author): array
    {
        return is_string($author) ? self::LEGACY_AUTHOR_NAMES[$author] : [];
    }

    /** @return list<string> the slugs of the post's live related episodes, in episode number order */
    private function publishedEpisodeSlugs(Post $post): array
    {
        return array_values($post->episodes
            ->filter(fn (Episode $episode): bool => $episode->isPublished())
            ->sortBy('episode_number')
            ->map(fn (Episode $episode): string => $episode->slug)
            ->all());
    }

    /**
     * Archives exported before a post could relate to several episodes carry a
     * single `episode_slug`; the `episode_slugs` list wins when both are present.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<array-key, mixed>
     */
    private function relatedEpisodeSlugs(array $attributes): array
    {
        $slugs = $attributes['episode_slugs'] ?? $attributes['episode_slug'] ?? [];

        return is_array($slugs) ? $slugs : [$slugs];
    }

    /** @param array<string, mixed> $attributes */
    private function importGuide(array $attributes): void
    {
        $guide = Guide::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($guide->trashed()) {
            $guide->restore();
        }

        $guide->fill([
            ...$this->onlyAttributes($attributes, self::GUIDE_FIELDS),
            'status' => PublishStatus::Published,
        ]);
        $guide->save();
        $this->importSeo($guide, $attributes);
        $this->importAuthors($guide, $attributes);
        if (is_array($attributes['tags'] ?? null)) {
            $guide->syncTagsWithType($attributes['tags'], 'content');
        }
    }

    /**
     * @return array{meta_title: ?string, meta_description: ?string}
     */
    private function seoAttributes(Episode|Post|Guide $record): array
    {
        return [
            'meta_title' => $record->seo?->title,
            'meta_description' => $record->seo?->description,
        ];
    }

    /** @param array<string, mixed> $attributes */
    private function importSeo(Episode|Post|Guide $record, array $attributes): void
    {
        if (! array_key_exists('meta_title', $attributes) && ! array_key_exists('meta_description', $attributes)) {
            return;
        }

        $record->seo()->updateOrCreate([], [
            'title' => $attributes['meta_title'] ?? null,
            'description' => $attributes['meta_description'] ?? null,
        ]);
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function attributes(Model $model, array $fields): array
    {
        return $this->onlyAttributes($model->getAttributes(), $fields);
    }

    /**
     * @param  Builder<Post>|Builder<Guide>|Builder<Episode>  $query
     * @param  list<array<string, mixed>>  $records
     */
    private function prunePublished(Builder $query, array $records): int
    {
        $slugs = collect($records)->pluck('slug')->all();

        $deleted = $query
            ->published()
            ->when($slugs !== [], fn (Builder $query): Builder => $query->whereNotIn('slug', $slugs))
            ->delete();

        if (! is_int($deleted)) {
            throw new \UnexpectedValueException('Public content pruning did not return a record count.');
        }

        return $deleted;
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

    private function validateMediaPath(mixed $path): string
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

    /** @param ValidatedArchive $archive */
    private function validateForPersistence(array $archive): void
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
            foreach (['featured_image_path', 'audio_path'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string', 'max:255'];
            }
            foreach (['source_url', 'transistor_url', 'audio_url', 'apple_url', 'spotify_url', 'youtube_url'] as $field) {
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
            $rules["{$type}.*.author"] = ['nullable', Rule::in(array_keys(self::LEGACY_AUTHOR_NAMES))];
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
        foreach (['apple_url', 'spotify_url', 'youtube_url', 'instagram_url', 'tiktok_url'] as $field) {
            $rules["podcast.{$field}"] = ['nullable', 'string', 'url:http,https', 'max:255'];
        }

        $validator = Validator::make($archive, $rules);
        if ($validator->fails()) {
            throw new InvalidArgumentException('Invalid public content archive: '.implode(' ', $validator->errors()->all()));
        }
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return ValidatedArchive
     */
    private function validate(array $archive): array
    {
        if (($archive['version'] ?? null) !== self::VERSION) {
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
            'version' => self::VERSION,
            'posts' => $this->validateRecords($archive['posts'] ?? null, 'posts', [...self::POST_FIELDS, ...self::POST_CATEGORY_FIELDS, ...self::AUTHOR_FIELDS, 'episode_slug', 'episode_slugs']),
            'guides' => $this->validateRecords($archive['guides'] ?? null, 'guides', [...self::GUIDE_FIELDS, ...self::AUTHOR_FIELDS]),
            'episodes' => $this->validateRecords($archive['episodes'] ?? null, 'episodes', self::EPISODE_FIELDS),
            'podcast' => $podcast === null ? null : $this->onlyAttributes($podcast, self::PODCAST_FIELDS),
        ];
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

            $validated[] = $this->onlyAttributes($attributes, [...$fields, ...self::SEO_FIELDS, 'tags']);
        }

        return $validated;
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function onlyAttributes(array $attributes, array $fields): array
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
