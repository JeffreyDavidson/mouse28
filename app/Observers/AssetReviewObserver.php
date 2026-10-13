<?php

namespace App\Observers;

use App\Enums\AssetReviewStatus;
use App\Models\AssetReview;

class AssetReviewObserver
{
    /** Changes to the reviewed material invalidate the previous decision. */
    public function updating(AssetReview $review): void
    {
        if ($review->isDirty(['title', 'version', 'source_post_id', 'files'])) {
            $review->status = AssetReviewStatus::Pending;
            $review->reviewed_by = null;
            $review->reviewed_at = null;
            $review->review_notes = null;
        }

        // Every saved change, including a decision, invalidates stale editor sessions.
        $review->revision += 1;
    }
}
