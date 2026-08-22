<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $published_at
 * @property-read string $author_initials
 * @property-read string $author_name
 * @property-read string $category_color
 * @property-read string $category_label
 * @property-read string|null $cover_image_url
 * @property-read string|null $og_image_url
 * @property-read int $reading_time
 * @property-read string $review_status
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'body',
    'source_url',
    'last_reviewed_at',
    'cover_image',
    'episode_id',
    'category',
    'author',
    'is_published',
    'published_at',
    'meta_title',
    'meta_description',
    'og_image',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    public const AUTHORS = [
        'jeffrey' => 'Jeffrey Davidson',
        'cassie' => 'Cassie Davidson',
        'both' => 'Jeffrey & Cassie',
    ];

    public const CATEGORIES = [
        'disney-tips' => 'Disney Tips',
        'park-accessibility' => 'Park Accessibility',
        'episode-recap' => 'Episode Recap',
        'family-life' => 'Family Life',
        'autism-awareness' => 'Autism Awareness',
        'disney-news' => 'Disney News',
        'food-reviews' => 'Food Reviews',
        'resort-reviews' => 'Resort Reviews',
        'disney-plus' => 'Disney+',
        'merchandise' => 'Merchandise',
        'general' => 'General',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    #[Scope]
    protected function reviewDue(Builder $query): void
    {
        $query->whereNotNull('source_url')
            ->where(function (Builder $query): void {
                $query->whereNull('last_reviewed_at')
                    ->orWhere('last_reviewed_at', '<', today()->subDays(config('mouse28.post_review_interval_days')));
            });
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
            $query->whereNull('excerpt')
                ->orWhere('excerpt', '')
                ->orWhereNull('body')
                ->orWhere('body', '')
                ->orWhereNull('cover_image')
                ->orWhere('cover_image', '')
                ->orWhereNull('meta_title')
                ->orWhere('meta_title', '')
                ->orWhereNull('meta_description')
                ->orWhere('meta_description', '')
                ->orWhere(function (Builder $query): void {
                    $query->where('is_published', true)->whereNull('published_at');
                })
                ->orWhere(function (Builder $query): void {
                    $query->whereNotNull('source_url')->whereNull('last_reviewed_at');
                })
                ->orWhere(function (Builder $query): void {
                    $query->whereNotNull('last_reviewed_at')
                        ->where(function (Builder $query): void {
                            $query->whereNull('source_url')->orWhere('source_url', '');
                        });
                });
        });
    }

    protected function authorName(): Attribute
    {
        return Attribute::make(get: function () {
            return self::AUTHORS[$this->author] ?? 'Mouse28 Team';
        });
    }

    protected function authorInitials(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->author === 'both') {
                return 'J&C';
            }
            $name = $this->author_name;

            return collect(explode(' ', $name))
                ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)
                ->join('');
        });
    }

    protected function readingTime(): Attribute
    {
        return Attribute::make(get: function () {
            $words = str_word_count(strip_tags($this->body ?? ''));

            return max(1, (int) ceil($words / 200));
        });
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return self::CATEGORIES[$this->category] ?? ucwords(str_replace('-', ' ', $this->category ?? ''));
        });
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->cover_image ? '/storage/'.$this->cover_image : null;
        });
    }

    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->og_image ? '/storage/'.$this->og_image : null;
        });
    }

    protected function categoryColor(): Attribute
    {
        return Attribute::make(get: function () {
            return match ($this->category) {
                'disney-tips' => 'bg-gold/20 text-gold',
                'park-accessibility' => 'bg-purple/20 text-purple',
                'episode-recap' => 'bg-emerald-500/20 text-emerald-600',
                'family-life' => 'bg-blue-500/20 text-blue-600',
                'autism-awareness' => 'bg-pink-500/20 text-pink-600',
                'disney-news' => 'bg-orange-500/20 text-orange-600',
                'food-reviews' => 'bg-amber-500/20 text-amber-600',
                'resort-reviews' => 'bg-teal-500/20 text-teal-600',
                'disney-plus' => 'bg-indigo-500/20 text-indigo-600',
                'merchandise' => 'bg-rose-500/20 text-rose-600',
                'general' => 'bg-slate-500/20 text-slate-600',
                default => 'bg-navy/10 text-navy',
            };
        });
    }

    protected function reviewStatus(): Attribute
    {
        return Attribute::make(get: function (): string {
            if (blank($this->source_url)) {
                return 'Not tracked';
            }

            return $this->isReviewDue() ? 'Review due' : 'Current';
        });
    }

    public function isReviewDue(): bool
    {
        return filled($this->source_url)
            && (! $this->last_reviewed_at
                || $this->last_reviewed_at->lt(today()->subDays(config('mouse28.post_review_interval_days'))));
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'last_reviewed_at' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
