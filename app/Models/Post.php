<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Enums\SourceReviewStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasAuthors;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasOgImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\IgnoresLegacyCategoryColumn;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Concerns\SyncsLegacyBody;
use App\Models\Concerns\SyncsLegacyPublishedFlag;
use App\Observers\PostObserver;
use Carbon\CarbonInterface;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property PublishStatus $status
 * @property string|null $content
 * @property int|null $category_id
 * @property-read Category|null $category
 * @property-read Collection<int, User> $authors
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $published_at
 * @property CarbonInterface|null $slug_locked_at
 * @property Carbon $updated_at
 * @property-read string $author_initials
 * @property-read string $author_name
 * @property-read string $category_label
 * @property string|null $featured_image_path
 * @property-read string|null $featured_image_url
 * @property-read string|null $og_image_url
 * @property-read int $reading_time
 *
 * @method static Builder<static> needsAttention()
 * @method static Builder<static> published()
 * @method static Builder<static> reviewDue()
 * @method static Builder<static> scheduled()
 * @method static Builder<static> unpublished()
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'source_url',
    'last_reviewed_at',
    'featured_image_path',
    'category_id',
    'status',
    'published_at',
    'meta_title',
    'meta_description',
    'og_image',
])]
#[ObservedBy(PostObserver::class)]
#[PublishingStatus]
class Post extends Model implements Publishable
{
    /** @use HasFactory<PostFactory> */
    use HasAuthors, HasFactory, HasFeaturedImage, HasOgImage, HasPublishingStatus, HasTagsUntilForceDeleted, IgnoresLegacyCategoryColumn, LocksSlugAfterPublication, ManagesStoredMedia, SoftDeletes, SyncsLegacyBody, SyncsLegacyPublishedFlag;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('editorial')
            ->logOnly([
                'title',
                'slug',
                'excerpt',
                'content',
                'source_url',
                'last_reviewed_at',
                'featured_image_path',
                'category_id',
                'status',
                'published_at',
                'meta_title',
                'meta_description',
                'og_image',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected function storedMediaAttributes(): array
    {
        return ['featured_image_path'];
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsToMany<Episode, $this> */
    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class);
    }

    /**
     * Posts whose official source has never been reviewed or was last reviewed longer
     * ago than the configured interval.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function reviewDue(Builder $query): void
    {
        $query->whereNotNull('source_url')
            ->where(function (Builder $query): void {
                $query->whereNull('last_reviewed_at')
                    ->orWhere('last_reviewed_at', '<', $this->reviewCutoff());
            });
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function needsAttention(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->whereNull('excerpt')
                ->orWhere('excerpt', '')
                ->orWhereNull('content')
                ->orWhere('content', '')
                ->orWhereNull('featured_image_path')
                ->orWhere('featured_image_path', '')
                ->orWhereNull('meta_title')
                ->orWhere('meta_title', '')
                ->orWhereNull('meta_description')
                ->orWhere('meta_description', '')
                ->orWhere(function (Builder $query): void {
                    $query->whereIn('status', [PublishStatus::Published, PublishStatus::Scheduled])->whereNull('published_at');
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

    /** @return Attribute<int, never> */
    protected function readingTime(): Attribute
    {
        return Attribute::make(get: function (): int {
            $words = str_word_count(strip_tags($this->content ?? ''));

            return max(1, (int) ceil($words / 200));
        });
    }

    /** @return Attribute<string, never> */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: fn (): string => $this->category->name ?? '');
    }

    public function isReviewDue(): bool
    {
        return filled($this->source_url)
            && (! $this->last_reviewed_at || $this->last_reviewed_at->lt($this->reviewCutoff()));
    }

    public function sourceReviewStatus(): SourceReviewStatus
    {
        if (blank($this->source_url)) {
            return SourceReviewStatus::NotTracked;
        }

        return $this->isReviewDue()
            ? SourceReviewStatus::ReviewDue
            : SourceReviewStatus::Current;
    }

    private function reviewCutoff(): CarbonInterface
    {
        return Date::today()->subDays(Config::integer('content.post_review_interval_days'));
    }

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'last_reviewed_at' => 'date',
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
            blank($this->content) ? 'Add post content' : null,
            blank($this->excerpt) ? 'Add an excerpt' : null,
            blank($this->category_id) ? 'Choose a category' : null,
        ]));
    }
}
