<?php

namespace App\Models;

use App\Contracts\Publishable;
use App\Models\Concerns\HasPublication;
use Carbon\CarbonInterface;
use Database\Factories\NewsletterIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property Carbon|null $published_at
 * @property Carbon|null $sent_at
 * @property CarbonInterface|null $slug_locked_at
 *
 * @method static Builder<static> drafts()
 * @method static Builder<static> published()
 * @method static Builder<static> scheduled()
 */
#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'is_published',
    'published_at',
    'sent_at',
])]
class NewsletterIssue extends Model implements Publishable
{
    /** @use HasFactory<NewsletterIssueFactory> */
    use HasFactory, HasPublication, SoftDeletes;

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
                'is_published',
                'published_at',
                'sent_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'sent_at' => 'datetime',
            'slug_locked_at' => 'datetime',
        ];
    }
}
