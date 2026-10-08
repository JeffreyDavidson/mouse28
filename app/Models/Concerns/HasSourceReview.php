<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\SourceReviewStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

/**
 * Flags content whose official source has never been reviewed, or was last reviewed
 * (`last_reviewed_at`) longer ago than the model's review interval. Records are
 * tracked for review unless the model narrows that with the two tracking hooks.
 */
trait HasSourceReview
{
    /** The number of days a review stays current. */
    abstract protected function sourceReviewIntervalDays(): int;

    /** Whether this record is tracked for source review. */
    protected function tracksSourceReview(): bool
    {
        return true;
    }

    /**
     * Limit a query to the records tracked for source review.
     *
     * @param  Builder<static>  $query
     */
    protected function whereTracksSourceReview(Builder $query): void {}

    /**
     * Tracked records that have never been reviewed or were last reviewed longer ago
     * than the review interval.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function reviewDue(Builder $query): void
    {
        $this->whereTracksSourceReview($query);

        $query->where(function (Builder $query): void {
            $query->whereNull('last_reviewed_at')
                ->orWhere('last_reviewed_at', '<', $this->reviewCutoff());
        });
    }

    public function isReviewDue(): bool
    {
        return $this->tracksSourceReview()
            && (! $this->last_reviewed_at || $this->last_reviewed_at->lt($this->reviewCutoff()));
    }

    public function sourceReviewStatus(): SourceReviewStatus
    {
        if (! $this->tracksSourceReview()) {
            return SourceReviewStatus::NotTracked;
        }

        return $this->isReviewDue()
            ? SourceReviewStatus::ReviewDue
            : SourceReviewStatus::Current;
    }

    private function reviewCutoff(): CarbonInterface
    {
        return Date::today()->subDays($this->sourceReviewIntervalDays());
    }
}
