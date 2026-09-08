<?php

namespace App\Models;

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
 * @property Carbon|null $published_at
 * @property-read string|null $cover_image_url
 * @property-read string $formatted_duration
 * @property-read string|null $og_image_url
 * @property-read string|null $transistor_embed_url
 *
 * @method static Builder<static> drafts()
 * @method static Builder<static> needsAttention()
 * @method static Builder<static> published()
 * @method static Builder<static> scheduled()
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
    'is_published',
    'published_at',
    'meta_title',
    'meta_description',
    'og_image',
])]
class Episode extends Model
{
    /** @use HasFactory<EpisodeFactory> */
    use HasFactory, SoftDeletes;

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
                'is_published',
                'published_at',
                'meta_title',
                'meta_description',
                'og_image',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    #[Scope]
    protected function drafts(Builder $query): void
    {
        $query->where('is_published', false);
    }

    #[Scope]
    protected function scheduled(Builder $query): void
    {
        $query->where('is_published', true)
            ->where('published_at', '>', now());
    }

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
                $query->where('is_published', true)->whereNull('published_at');
            });
        });
    }

    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->og_image ? '/storage/'.$this->og_image : null;
        });
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->cover_image ? '/storage/'.$this->cover_image : null;
        });
    }

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

    protected function formattedDuration(): Attribute
    {
        return Attribute::make(get: function () {
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
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
