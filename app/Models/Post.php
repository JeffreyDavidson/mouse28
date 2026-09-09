<?php

namespace App\Models;

use App\Enums\ContentAuthor;
use App\Enums\PostCategory;
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
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property ContentAuthor|null $author
 * @property PostCategory|null $category
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $published_at
 * @property-read string $author_initials
 * @property-read string $author_name
 * @property-read string $category_label
 * @property-read string|null $cover_image_url
 * @property-read string|null $og_image_url
 * @property-read int $reading_time
 * @property-read string $review_status
 *
 * @method static Builder<static> drafts()
 * @method static Builder<static> needsAttention()
 * @method static Builder<static> published()
 * @method static Builder<static> reviewDue()
 * @method static Builder<static> scheduled()
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

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('editorial')
            ->logOnly([
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
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<Episode, $this> */
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
        return Attribute::make(get: fn () => $this->author?->getLabel() ?? 'Mouse28 Team');
    }

    protected function authorInitials(): Attribute
    {
        return Attribute::make(get: fn (): string => Str::initials($this->author_name, capitalize: true));
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
        return Attribute::make(get: fn (): string => $this->category?->getLabel() ?? '');
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->cover_image ? '/storage/'.$this->cover_image : null);
    }

    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: fn () => $this->og_image ? '/storage/'.$this->og_image : null);
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
            'author' => ContentAuthor::class,
            'category' => PostCategory::class,
            'is_published' => 'boolean',
            'last_reviewed_at' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
