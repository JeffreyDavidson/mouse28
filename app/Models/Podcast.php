<?php

namespace App\Models;

use App\Models\Concerns\LogsEditorialActivity;
use App\Observers\PodcastObserver;
use Database\Factories\PodcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

/**
 * A show, in The Laravel Architect's multi-show model. Mouse28 runs one show, found by
 * `App\Support\PrimaryPodcast`.
 *
 * @property string|null $cover_image_path
 * @property bool $is_active
 * @property int $sort_order
 * @property-read Collection<int, Episode> $publishedEpisodes
 */
#[Fillable([
    'name',
    'slug',
    'description',
    'long_description',
    'cover_image_path',
    'color',
    'apple_url',
    'spotify_url',
    'youtube_url',
    'is_active',
    'sort_order',
])]
#[ObservedBy(PodcastObserver::class)]
#[Sluggable(from: 'name', maxLength: 255)]
class Podcast extends Model
{
    /** @use HasFactory<PodcastFactory> */
    use HasFactory, LogsEditorialActivity;

    use SoftDeletes {
        performDeleteOnModel as performSoftDeleteOnModel;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Runs only after the delete is confirmed: trash the episodes with the podcast, or
     * permanently delete all of them (with their own cleanup) before the podcast row goes.
     * Trashed episodes are then stamped with the podcast's deleted_at, so a delete that
     * spans several seconds still lets PodcastObserver::restoring() bring them all back.
     */
    protected function performDeleteOnModel(): void
    {
        $episodes = $this->isForceDeleting()
            ? $this->episodes()
                ->withTrashed()
            : $this->episodes();
        $deletedEpisodeIds = [];

        foreach ($episodes->lazyById() as $episode) {
            $deleted = $this->isForceDeleting()
                ? $episode->forceDelete()
                : $episode->delete();

            if ($deleted !== true) {
                throw new \RuntimeException('Podcast deletion was cancelled because an episode could not be deleted.');
            }

            $deletedEpisodeIds[] = $episode->getKey();
        }

        $this->performSoftDeleteOnModel();

        if ($this->isForceDeleting() || $deletedEpisodeIds === []) {
            return;
        }

        $this->episodes()
            ->onlyTrashed()
            ->whereKey($deletedEpisodeIds)
            ->toBase()
            ->update([
                'deleted_at' => $this->fromDateTime($this->getAttribute($this->getDeletedAtColumn())),
            ]);
    }

    /** @return HasMany<Episode, $this> */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /** @return HasMany<Episode, $this> */
    public function publishedEpisodes(): HasMany
    {
        return $this
            ->episodes()
            ->published();
    }

    public function latestEpisode(): ?Episode
    {
        return $this
            ->publishedEpisodes()
            ->latest('published_at')
            ->first();
    }

    /** @param Builder<Podcast> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
