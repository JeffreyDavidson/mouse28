<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Concerns\ScopesMissingSeo;
use App\Observers\EpisodeObserver;
use Carbon\CarbonInterface;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use RalphJSmit\Laravel\SEO\Models\SEO;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property-read SEO $seo
 * @property int|null $podcast_id
 * @property string|null $guest_name
 * @property string|null $guest_title
 * @property string|null $guest_url
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property CarbonInterface|null $slug_locked_at
 * @property Carbon $updated_at
 * @property string|null $featured_image_path
 * @property-read string|null $featured_image_url
 *
 * @method static Builder<static> needsAttention()
 * @method static Builder<static> published()
 * @method static Builder<static> scheduled()
 * @method static Builder<static> unpublished()
 */
#[Fillable([
    'podcast_id',
    'title',
    'slug',
    'description',
    'show_notes',
    'transcript',
    'episode_number',
    'season_number',
    'transistor_url',
    'youtube_url',
    'guest_name',
    'guest_title',
    'guest_url',
    'duration_seconds',
    'featured_image_path',
    'status',
    'published_at',
])]
#[ObservedBy(EpisodeObserver::class)]
#[PublishingStatus]
class Episode extends Model implements Publishable
{
    /** @use HasFactory<EpisodeFactory> */
    use HasFactory, HasFeaturedImage, HasPublishingStatus, HasSEO, HasTagsUntilForceDeleted, LocksSlugAfterPublication, ManagesStoredMedia, ScopesMissingSeo, SoftDeletes;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('editorial')
            ->logOnly([
                'title',
                'slug',
                'description',
                'show_notes',
                'transcript',
                'episode_number',
                'season_number',
                'transistor_url',
                'youtube_url',
                'podcast_id',
                'guest_name',
                'guest_title',
                'guest_url',
                'duration_seconds',
                'featured_image_path',
                'status',
                'published_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['featured_image_path'];
    }

    /** @return BelongsTo<Podcast, $this> */
    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function needsAttention(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            foreach (['description', 'show_notes', 'featured_image_path', 'duration_seconds'] as $column) {
                $query->orWhereNull($column);

                if ($column !== 'duration_seconds') {
                    $query->orWhere($column, '');
                }
            }

            $query->orWhere(fn (Builder $query) => $query->missingSeo());

            // No playable media: no Transistor share link and no YouTube video (the readiness checklist and
            // publishing rule use `transistorEmbedUrl()`; LIKE matches its share-link prefix).
            $query->orWhere(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereNull('transistor_url')->orWhere('transistor_url', 'not like', 'https://share.transistor.fm/s/%');
                })->where(function (Builder $query): void {
                    $query->whereNull('youtube_url')->orWhere('youtube_url', '');
                });
            });

            $query->orWhere(function (Builder $query): void {
                $query->whereIn('status', [PublishStatus::Published, PublishStatus::Scheduled])->whereNull('published_at');
            });
        });
    }

    /** The embeddable Transistor player for a share link (https://share.transistor.fm/s/{id}); null otherwise. */
    public function transistorEmbedUrl(): ?string
    {
        $url = $this->getAttribute('transistor_url');
        $matches = [];

        if (! is_string($url) || preg_match('/\Ahttps:\/\/share\.transistor\.fm\/s\/([a-zA-Z0-9]+)\/?\z/', $url, $matches) !== 1) {
            return null;
        }

        return "https://share.transistor.fm/e/{$matches[1]}";
    }

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'slug_locked_at' => 'datetime',
        ];
    }

    /**
     * The details that must be present before publishing; the rest of the readiness checklist is advisory.
     *
     * @return list<string>
     */
    public function publishingIssues(): array
    {
        return array_values(array_filter([
            blank($this->description) ? 'Add a description' : null,
            $this->transistorEmbedUrl() === null && blank($this->youtube_url) ? 'Add a Transistor share link or a YouTube video' : null,
        ]));
    }
}
