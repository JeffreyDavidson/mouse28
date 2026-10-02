<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasCoverImages;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\SyncsLegacyPublishedFlag;
use Carbon\CarbonInterface;
use Database\Factories\EpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property CarbonInterface|null $slug_locked_at
 * @property Carbon $updated_at
 * @property-read string|null $cover_image_url
 * @property-read string $formatted_duration
 * @property-read string|null $og_image_url
 * @property-read string|null $transistor_embed_url
 *
 * @method static Builder<static> needsAttention()
 * @method static Builder<static> published()
 * @method static Builder<static> scheduled()
 * @method static Builder<static> unpublished()
 */
#[Fillable([
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
    'status',
    'published_at',
    'meta_title',
    'meta_description',
    'og_image',
])]
#[PublishingStatus]
class Episode extends Model implements Publishable
{
    /** @use HasFactory<EpisodeFactory> */
    use HasCoverImages, HasFactory, HasPublishingStatus, HasTagsUntilForceDeleted, LocksSlugAfterPublication, SoftDeletes, SyncsLegacyPublishedFlag;

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
                'apple_url',
                'spotify_url',
                'youtube_url',
                'duration_seconds',
                'cover_image',
                'status',
                'published_at',
                'meta_title',
                'meta_description',
                'og_image',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function needsAttention(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            foreach (['description', 'show_notes', 'cover_image', 'duration_seconds', 'meta_title', 'meta_description'] as $column) {
                $query->orWhereNull($column);

                if ($column !== 'duration_seconds') {
                    $query->orWhere($column, '');
                }
            }

            $query->orWhere(function (Builder $query): void {
                $query->whereIn('status', [PublishStatus::Published, PublishStatus::Scheduled])->whereNull('published_at');
            });
        });
    }

    /** @return Attribute<string|null, never> */
    protected function transistorEmbedUrl(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            if (! is_string($this->transistor_url)) {
                return null;
            }

            $matches = [];

            if (preg_match('/\Ahttps:\/\/share\.transistor\.fm\/s\/([a-zA-Z0-9]+)\/?\z/', $this->transistor_url, $matches) !== 1) {
                return null;
            }

            return "https://share.transistor.fm/e/{$matches[1]}";
        });
    }

    /** @return Attribute<string, never> */
    protected function formattedDuration(): Attribute
    {
        return Attribute::make(get: function (): string {
            if (! $this->duration_seconds) {
                return '';
            }
            $minutes = floor($this->duration_seconds / 60);
            $seconds = $this->duration_seconds % 60;

            return sprintf('%d:%02d', $minutes, $seconds);
        });
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
            blank($this->transistor_url) ? 'Add the Transistor episode URL' : null,
        ]));
    }
}
