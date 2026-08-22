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
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $published_at
 * @property-read string $formatted_duration
 * @property-read string|null $og_image_url
 */
#[Fillable([
    'title',
    'slug',
    'description',
    'show_notes',
    'transcript',
    'episode_number',
    'season_number',
    'audio_url',
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
    use HasFactory;

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

    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->og_image ? '/storage/'.$this->og_image : null;
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
