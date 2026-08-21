<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    /** @use HasFactory<\Database\Factories\GuideFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'accessibility' => 'Accessibility',
        'park-strategy' => 'Park Strategy',
        'food-reviews' => 'Food & Reviews',
        'family-planning' => 'Family Planning',
    ];

    protected $fillable = [
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
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'last_reviewed_at' => 'date',
        'published_at' => 'datetime',
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

    public function getAuthorNameAttribute(): string
    {
        return Post::AUTHORS[$this->author] ?? 'Mouse28 Team';
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? str($this->category)->headline();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? '/storage/'.$this->cover_image : null;
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->og_image ? '/storage/'.$this->og_image : null;
    }

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
    }

    public function getReviewStatusAttribute(): string
    {
        return $this->isReviewDue() ? 'Review due' : 'Current';
    }

    public function isReviewDue(): bool
    {
        return ! $this->last_reviewed_at
            || $this->last_reviewed_at->lt(today()->subDays(config('mouse28.guide_review_interval_days')));
    }
}
