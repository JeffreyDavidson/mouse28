<?php

namespace App\Support;

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\PostCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        'audio_url',
        'audio_path',
        'apple_url',
        'spotify_url',
        'youtube_url',
        'duration_seconds',
        'cover_image',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    private const array POST_FIELDS = [
        'title',
        'slug',
        'excerpt',
        'body',
        'source_url',
        'last_reviewed_at',
        'cover_image',
        'category',
        'author',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    private const array GUIDE_FIELDS = [
        'title',
        'slug',
        'excerpt',
        'body',
        'category',
        'author',
        'cover_image',
        'source_url',
        'last_reviewed_at',
        'published_at',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    private const array PODCAST_FIELDS = [
        'name',
        'description',
        'cover_image',
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
            ->with('tags')
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...self::EPISODE_FIELDS])
            ->map(fn (Episode $episode): array => [...$this->attributes($episode, self::EPISODE_FIELDS), 'tags' => $episode->tagsWithType('content')->pluck('name')->all()])
            ->values()
            ->all();
        $episodeSlugs = Episode::query()->published()->pluck('slug', 'id');

        $posts = Post::query()
            ->with('tags')
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...self::POST_FIELDS, 'episode_id'])
            ->map(fn (Post $post): array => [
                ...$this->attributes($post, self::POST_FIELDS),
                'episode_slug' => $episodeSlugs->get($post->episode_id),
                'tags' => $post->tagsWithType('content')->pluck('name')->all(),
            ])
            ->values()
            ->all();

        $guides = Guide::query()
            ->with('tags')
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...self::GUIDE_FIELDS])
            ->map(fn (Guide $guide): array => [...$this->attributes($guide, self::GUIDE_FIELDS), 'tags' => $guide->tagsWithType('content')->pluck('name')->all()])
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
                if (! $record->is_published || $record->published_at === null || $record->published_at->isFuture()) {
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
                foreach (['audio_path', 'cover_image', 'og_image'] as $field) {
                    if (filled($attributes[$field] ?? null)) {
                        $paths[] = $this->validateMediaPath($attributes[$field]);
                    }
                }
            }
        }

        $podcastCover = $archive['podcast']['cover_image'] ?? null;

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
            'is_published' => true,
        ]);
        $episode->save();
        if (is_array($attributes['tags'] ?? null)) {
            $episode->syncTagsWithType($attributes['tags'], 'content');
        }
    }

    /** @param array<string, mixed> $attributes */
    private function importPost(array $attributes): void
    {
        $episodeId = filled($attributes['episode_slug'] ?? null)
            ? Episode::query()->where('slug', $attributes['episode_slug'])->value('id')
            : null;
        $post = Post::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($post->trashed()) {
            $post->restore();
        }

        $post->fill([
            ...$this->onlyAttributes($attributes, self::POST_FIELDS),
            'episode_id' => $episodeId,
            'is_published' => true,
        ]);
        $post->save();
        if (is_array($attributes['tags'] ?? null)) {
            $post->syncTagsWithType($attributes['tags'], 'content');
        }
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
            'is_published' => true,
        ]);
        $guide->save();
        if (is_array($attributes['tags'] ?? null)) {
            $guide->syncTagsWithType($attributes['tags'], 'content');
        }
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
            foreach (['excerpt', 'body', 'description', 'show_notes', 'transcript', 'meta_title', 'meta_description'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string'];
            }
            foreach (['cover_image', 'og_image', 'audio_path'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string', 'max:255'];
            }
            foreach (['source_url', 'transistor_url', 'audio_url', 'apple_url', 'spotify_url', 'youtube_url'] as $field) {
                $rules["{$type}.*.{$field}"] = ['nullable', 'string', 'url:http,https', 'max:255'];
            }
        }
        $rules['posts.*.episode_slug'] = ['nullable', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
        $rules['posts.*.body'] = ['present', 'string'];
        $rules['guides.*.body'] = ['present', 'string'];
        $rules['posts.*.author'] = ['nullable', Rule::enum(ContentAuthor::class)];
        $rules['posts.*.category'] = ['nullable', Rule::enum(PostCategory::class)];
        $rules['guides.*.author'] = ['required', Rule::enum(ContentAuthor::class)];
        $rules['guides.*.category'] = ['required', Rule::enum(GuideCategory::class)];
        $rules['episodes.*.episode_number'] = ['required', 'integer', 'min:0', 'max:2147483647', 'distinct'];
        $rules['episodes.*.season_number'] = ['nullable', 'integer', 'min:0', 'max:4294967295'];
        $rules['episodes.*.duration_seconds'] = ['nullable', 'integer', 'min:0', 'max:2147483647'];
        $rules['podcast.name'] = ['required_with:podcast', 'string', 'max:255'];
        $rules['podcast.description'] = ['nullable', 'string'];
        $rules['podcast.cover_image'] = ['nullable', 'string', 'max:255'];
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

        if (filled($archive['podcast']['cover_image'] ?? null)) {
            $this->validateMediaPath($archive['podcast']['cover_image']);
        }

        return [
            'version' => self::VERSION,
            'posts' => $this->validateRecords($archive['posts'] ?? null, 'posts', [...self::POST_FIELDS, 'episode_slug']),
            'guides' => $this->validateRecords($archive['guides'] ?? null, 'guides', self::GUIDE_FIELDS),
            'episodes' => $this->validateRecords($archive['episodes'] ?? null, 'episodes', self::EPISODE_FIELDS),
            'podcast' => $archive['podcast'] === null ? null : $this->onlyAttributes($archive['podcast'], self::PODCAST_FIELDS),
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

            foreach (['cover_image', 'og_image', 'audio_path'] as $field) {
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

            $validated[] = $this->onlyAttributes($attributes, [...$fields, 'tags']);
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
