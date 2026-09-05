<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PublicContentArchive
{
    private const VERSION = 1;

    private const EPISODE_FIELDS = [
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

    private const POST_FIELDS = [
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

    private const GUIDE_FIELDS = [
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

    private const PODCAST_FIELDS = [
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
            ->published()
            ->orderBy('published_at')
            ->get(self::EPISODE_FIELDS)
            ->map(fn (Episode $episode): array => $this->attributes($episode, self::EPISODE_FIELDS))
            ->values()
            ->all();
        $episodeSlugs = Episode::query()->published()->pluck('slug', 'id');

        $posts = Post::query()
            ->published()
            ->orderBy('published_at')
            ->get([...self::POST_FIELDS, 'episode_id'])
            ->map(function (Post $post) use ($episodeSlugs): array {
                return [
                    ...$this->attributes($post, self::POST_FIELDS),
                    'episode_slug' => $episodeSlugs->get((int) $post->getAttribute('episode_id')),
                ];
            })
            ->values()
            ->all();

        $guides = Guide::query()
            ->published()
            ->orderBy('published_at')
            ->get(self::GUIDE_FIELDS)
            ->map(fn (Guide $guide): array => $this->attributes($guide, self::GUIDE_FIELDS))
            ->values()
            ->all();

        $podcast = Podcast::query()->first();

        return [
            'version' => self::VERSION,
            'exported_at' => now()->toAtomString(),
            'episodes' => $episodes,
            'posts' => $posts,
            'guides' => $guides,
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

    /**
     * @param  array<string, mixed>  $archive
     * @return list<string>
     */
    public function mediaPaths(array $archive): array
    {
        $this->validate($archive);

        $paths = collect(['episodes', 'posts', 'guides'])
            ->flatMap(fn (string $contentType) => collect($archive[$contentType])->flatMap(
                fn (array $attributes): array => array_values(
                    Arr::only($attributes, ['audio_path', 'cover_image', 'og_image']),
                ),
            ))
            ->when(
                is_array($archive['podcast']),
                fn ($paths) => $paths->push($archive['podcast']['cover_image'] ?? null),
            )
            ->filter(fn (mixed $path): bool => filled($path))
            ->map(fn (mixed $path): string => $this->validateMediaPath($path))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return array{posts: int, guides: int, episodes: int, podcast: int, removed_posts?: int, removed_guides?: int, removed_episodes?: int}
     */
    private function persist(array $archive, bool $prunePublished = false): array
    {
        $this->validate($archive);

        return DB::transaction(function () use ($archive, $prunePublished): array {
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
                $podcast->fill(Arr::only($archive['podcast'], self::PODCAST_FIELDS));
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
            ...Arr::only($attributes, self::EPISODE_FIELDS),
            'is_published' => true,
        ]);
        $episode->save();
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
            ...Arr::only($attributes, self::POST_FIELDS),
            'episode_id' => $episodeId,
            'is_published' => true,
        ]);
        $post->save();
    }

    /** @param array<string, mixed> $attributes */
    private function importGuide(array $attributes): void
    {
        $guide = Guide::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($guide->trashed()) {
            $guide->restore();
        }

        $guide->fill([
            ...Arr::only($attributes, self::GUIDE_FIELDS),
            'is_published' => true,
        ]);
        $guide->save();
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function attributes(Model $model, array $fields): array
    {
        return Arr::only($model->getAttributes(), $fields);
    }

    /**
     * @param  Builder<Post>|Builder<Guide>|Builder<Episode>  $query
     * @param  list<array<string, mixed>>  $records
     */
    private function prunePublished(Builder $query, array $records): int
    {
        $slugs = collect($records)->pluck('slug')->all();

        return $query
            ->published()
            ->when($slugs !== [], fn ($query) => $query->whereNotIn('slug', $slugs))
            ->delete();
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

    /** @param array<string, mixed> $archive */
    private function validate(array $archive): void
    {
        if (($archive['version'] ?? null) !== self::VERSION) {
            throw new InvalidArgumentException('The public content archive version is not supported.');
        }

        foreach (['posts', 'guides', 'episodes'] as $contentType) {
            if (! isset($archive[$contentType]) || ! is_array($archive[$contentType])) {
                throw new InvalidArgumentException("The public content archive is missing {$contentType}.");
            }

            foreach ($archive[$contentType] as $attributes) {
                if (! is_array($attributes) || blank($attributes['slug'] ?? null)) {
                    throw new InvalidArgumentException("The public content archive contains invalid {$contentType}.");
                }
            }
        }

        if (! array_key_exists('podcast', $archive) || (! is_array($archive['podcast']) && $archive['podcast'] !== null)) {
            throw new InvalidArgumentException('The public content archive contains invalid podcast metadata.');
        }
    }
}
