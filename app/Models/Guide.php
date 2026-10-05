<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Enums\SourceReviewStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasAuthors;
use App\Models\Concerns\HasFeaturedImage;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\HasTagsUntilForceDeleted;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Concerns\ScopesMissingSeo;
use App\Observers\GuideObserver;
use Carbon\CarbonInterface;
use Database\Factories\GuideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use RalphJSmit\Laravel\SEO\Models\SEO;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property-read SEO $seo
 * @property PublishStatus $status
 * @property string|null $content
 * @property GuideCategory $category
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
    'category',
    'featured_image_path',
    'source_url',
    'last_reviewed_at',
    'status',
    'published_at',
])]
#[ObservedBy(GuideObserver::class)]
#[PublishingStatus]
class Guide extends Model implements Publishable
{
    /** @use HasFactory<GuideFactory> */
    use HasAuthors, HasFactory, HasFeaturedImage, HasPublishingStatus, HasSEO, HasTagsUntilForceDeleted, LocksSlugAfterPublication, ManagesStoredMedia, ScopesMissingSeo, SoftDeletes;

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
                'category',
                'featured_image_path',
                'source_url',
                'last_reviewed_at',
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

    /**
     * Guides that have never been reviewed or were last reviewed longer ago than the
     * configured interval.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function reviewDue(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->whereNull('last_reviewed_at')
                ->orWhere('last_reviewed_at', '<', $this->reviewCutoff());
        });
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function needsAttention(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            foreach (['excerpt', 'content', 'featured_image_path', 'source_url', 'last_reviewed_at'] as $column) {
                $query->orWhereNull($column);

                if ($column !== 'last_reviewed_at') {
                    $query->orWhere($column, '');
                }
            }

            $query->orWhere(fn (Builder $query) => $query->missingSeo());

            $query->orWhere(function (Builder $query): void {
                $query->whereIn('status', [PublishStatus::Published, PublishStatus::Scheduled])->whereNull('published_at');
            });
        });
    }

    /** @return Attribute<string, never> */
    protected function categoryLabel(): Attribute
    {
        return Attribute::make(get: fn (): string => $this->category->getLabel());
    }

    /** @return Attribute<int, never> */
    protected function readingTime(): Attribute
    {
        return Attribute::make(get: fn (): int => max(1, (int) ceil(str_word_count(strip_tags($this->content ?? '')) / 200)));
    }

    public function isReviewDue(): bool
    {
        return ! $this->last_reviewed_at || $this->last_reviewed_at->lt($this->reviewCutoff());
    }

    /**
     * Guides are always tracked for review, so unlike posts they are never "not tracked".
     */
    public function sourceReviewStatus(): SourceReviewStatus
    {
        return $this->isReviewDue()
            ? SourceReviewStatus::ReviewDue
            : SourceReviewStatus::Current;
    }

    private function reviewCutoff(): CarbonInterface
    {
        return Date::today()->subDays(Config::integer('content.guide_review_interval_days'));
    }

    protected function casts(): array
    {
        return [
            'category' => GuideCategory::class,
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
            blank($this->content) ? 'Add guide content' : null,
            blank($this->excerpt) ? 'Add an excerpt' : null,
            blank($this->source_url) ? 'Add an official source' : null,
            blank($this->last_reviewed_at) ? 'Set the review date' : null,
        ]));
    }
}
