<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Enums\PublishStatus;
use App\Models\Attributes\PublishingStatus;
use App\Models\Concerns\HasDraftScope;
use App\Models\Concerns\HasPublishingStatus;
use App\Models\Concerns\LocksSlugAfterPublication;
use App\Models\Concerns\LogsEditorialActivity;
use App\Models\Concerns\ScopesNewestFirst;
use Carbon\CarbonInterface;
use Database\Factories\NewsletterIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use NunoMaduro\LaravelSluggable\Attributes\Sluggable;

/**
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $sent_at
 * @property CarbonInterface|null $slug_locked_at
 *
 * @method static Builder<static> drafts()
 * @method static Builder<static> newestFirst()
 * @method static Builder<static> published()
 * @method static Builder<static> scheduled()
 * @method static Builder<static> unpublished()
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'status',
    'published_at',
    'sent_at',
])]
#[PublishingStatus]
#[Sluggable(from: 'title', maxLength: 255)]
class NewsletterIssue extends Model implements Publishable
{
    /** @use HasFactory<NewsletterIssueFactory> */
    use HasDraftScope, HasFactory, HasPublishingStatus, LocksSlugAfterPublication, LogsEditorialActivity, ScopesNewestFirst, SoftDeletes;

    /** @return HasMany<NewsletterDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function wasSent(): bool
    {
        return $this->sent_at !== null;
    }

    /**
     * The details that must be present before publishing.
     *
     * @return list<string>
     */
    public function publishingIssues(): array
    {
        return array_values(array_filter([
            blank($this->content) ? 'Add issue content' : null,
        ]));
    }

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'sent_at' => 'datetime',
            'slug_locked_at' => 'datetime',
        ];
    }
}
