<?php

namespace App\Models;

use Database\Factories\GuideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $published_at
 * @property-read string $author_name
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
    'category',
    'author',
    'cover_image',
    'source_url',
    'last_reviewed_at',
    'is_published',
    'published_at',
    'meta_title',
    'meta_description',
    'og_image',
])]
class Guide extends Model
{
    /** @use HasFactory<GuideFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'accessibility' => 'Accessibility',
        'park-strategy' => 'Park Strategy',
        'food-reviews' => 'Food & Reviews',
        'family-planning' => 'Family Planning',
    ];

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
        $query->where(function (Builder $query): void {
            $query->whereNull('last_reviewed_at')
                ->orWhere('last_reviewed_at', '<', today()->subDays(config('mouse28.guide_review_interval_days')));
        });
    }

    protected function authorName(): Attribute
    {
        return Attribute::make(get: function () {
            return Post::AUTHORS[$this->author] ?? 'Mouse28 Team';
        });
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: function () {
            return self::CATEGORIES[$this->category] ?? str($this->category)->headline();
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

    protected function readingTime(): Attribute
    {
        return Attribute::make(get: function () {
            return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
        });
    }

    protected function reviewStatus(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->isReviewDue() ? 'Review due' : 'Current';
        });
    }

    public function isReviewDue(): bool
    {
        return ! $this->last_reviewed_at
            || $this->last_reviewed_at->lt(today()->subDays(config('mouse28.guide_review_interval_days')));
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
