<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Imports a public content archive in one transaction. `import()` adds and updates
 * records; `sync()` also removes local published records the archive no longer holds,
 * and refuses when a local draft shares an identity with an archived record. Posts and
 * guides match by slug, episodes by episode number. Private fields (such as review
 * notes) are never written.
 *
 * @phpstan-import-type ValidatedArchive from PublicContentArchiveValidator
 */
class PublicContentArchiveImporter
{
    public function __construct(
        private readonly PublicContentArchiveValidator $validator,
        private readonly PublicContentArchiveRelations $relations,
    ) {}

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
        $archive = $this->validator->validate($archive);
        $this->validator->validateForPersistence($archive);

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
        $archive = $this->validator->validate($archive);

        $paths = [];

        foreach ([$archive['episodes'], $archive['posts'], $archive['guides']] as $records) {
            foreach ($records as $attributes) {
                foreach (['featured_image_path'] as $field) {
                    if (filled($attributes[$field] ?? null)) {
                        $paths[] = $this->validator->validateMediaPath($attributes[$field]);
                    }
                }
            }
        }

        $podcastCover = $archive['podcast']['cover_image_path'] ?? null;

        if (filled($podcastCover)) {
            $paths[] = $this->validator->validateMediaPath($podcastCover);
        }

        return array_values(collect($paths)->unique()
            ->sort()
            ->all());
    }

    /**
     * @param  array<string, mixed>  $archive
     * @return ($prunePublished is true ? array{posts: int, guides: int, episodes: int, podcast: int, removed_posts: int, removed_guides: int, removed_episodes: int} : array{posts: int, guides: int, episodes: int, podcast: int})
     */
    private function persist(array $archive, bool $prunePublished = false): array
    {
        $archive = $this->validator->validate($archive);
        $this->validator->validateForPersistence($archive);

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
                $podcast->fill(PublicContentArchiveSchema::only($archive['podcast'], PublicContentArchiveSchema::PODCAST_FIELDS));
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
            ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::EPISODE_FIELDS),
            'status' => PublishStatus::Published,
        ]);
        $episode->save();
        $this->relations->syncSeo($episode, $attributes);
        $this->relations->syncTags($episode, $attributes);
    }

    /** @param array<string, mixed> $attributes */
    private function importPost(array $attributes): void
    {
        $post = Post::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($post->trashed()) {
            $post->restore();
        }

        $post->fill([
            ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::POST_FIELDS),
            'status' => PublishStatus::Published,
        ]);
        $this->relations->syncCategory($post, $attributes);
        $post->save();
        $this->relations->syncSeo($post, $attributes);
        $this->relations->syncAuthors($post, $attributes);
        $this->relations->syncEpisodes($post, $attributes);
        $this->relations->syncTags($post, $attributes);
    }

    /** @param array<string, mixed> $attributes */
    private function importGuide(array $attributes): void
    {
        $guide = Guide::withTrashed()->firstOrNew(['slug' => $attributes['slug']]);

        if ($guide->trashed()) {
            $guide->restore();
        }

        $guide->fill([
            ...PublicContentArchiveSchema::only($attributes, PublicContentArchiveSchema::GUIDE_FIELDS),
            'status' => PublishStatus::Published,
        ]);
        $guide->save();
        $this->relations->syncSeo($guide, $attributes);
        $this->relations->syncAuthors($guide, $attributes);
        $this->relations->syncTags($guide, $attributes);
    }

    /**
     * @param  Builder<Post>|Builder<Guide>|Builder<Episode>  $query
     * @param  list<array<string, mixed>>  $records
     */
    private function prunePublished(Builder $query, array $records): int
    {
        $slugs = collect($records)->pluck('slug')
            ->all();

        $deleted = $query
            ->published()
            ->when($slugs !== [], fn (Builder $query): Builder => $query->whereNotIn('slug', $slugs))
            ->delete();

        if (! is_int($deleted)) {
            throw new UnexpectedValueException('Public content pruning did not return a record count.');
        }

        return $deleted;
    }
}
