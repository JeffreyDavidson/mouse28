<?php

namespace App\Actions;

use App\Enums\AssetReviewStatus;
use App\Models\AssetReview;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReviewAsset
{
    /** Persist a decision only for the saved revision the reviewer actually opened. */
    public function handle(AssetReview $review, User $reviewer, int $revision, AssetReviewStatus $status, ?string $notes): AssetReview
    {
        Gate::forUser($reviewer)->authorize('update', $review);
        abort_unless($reviewer->is_admin, 403);

        return DB::transaction(function () use ($review, $reviewer, $revision, $status, $notes): AssetReview {
            $saved = AssetReview::query()
                ->lockForUpdate()
                ->findOrFail($review->id);

            if ($saved->revision !== $revision) {
                throw ValidationException::withMessages(['review' => 'This set changed since you opened it. Reload and review the latest version.']);
            }

            if ($status === AssetReviewStatus::Pending) {
                throw ValidationException::withMessages(['review' => 'Choose Approve or Request changes.']);
            }

            if ($status === AssetReviewStatus::ChangesRequested && blank($notes)) {
                throw ValidationException::withMessages(['review_notes' => 'Describe the changes you need.']);
            }

            if ($status === AssetReviewStatus::Approved) {
                if ($saved->files === []) {
                    throw ValidationException::withMessages(['review' => 'Upload the review images before approving.']);
                }

                foreach (array_keys($saved->files) as $index) {
                    $path = $saved->filePath($index);
                    if ($path === null || ! Storage::disk('local')->exists($path) || ! in_array(Storage::disk('local')->mimeType($path), ['image/png', 'image/jpeg', 'image/webp'], true)) {
                        throw ValidationException::withMessages(['review' => 'A preview is missing. Replace the missing image before approving.']);
                    }
                }
            }

            $saved->status = $status;
            $saved->review_notes = $notes;
            $saved->reviewed_by = $reviewer->id;
            $saved->reviewed_at = Date::now();
            $saved->save();

            return $saved;
        });
    }
}
