<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Podcast;
use App\Services\ResponsiveImageLifecycle;
use Illuminate\Support\Facades\Storage;

/**
 * The single-row podcast has no soft deletes, so it cannot use `ManagesStoredMedia`
 * (whose cleanup hangs off `forceDeleted`); this observer removes a replaced or
 * deleted original after commit itself, alongside the responsive variants.
 */
class PodcastObserver
{
    public function __construct(private readonly ResponsiveImageLifecycle $lifecycle) {}

    public function created(Podcast $podcast): void
    {
        $this->lifecycle->created($podcast, 'cover_image_path', 'podcast');
    }

    public function updated(Podcast $podcast): void
    {
        $this->lifecycle->updated($podcast, 'cover_image_path', 'podcast');

        if ($podcast->wasChanged('cover_image_path')) {
            $this->deleteOriginalAfterCommit($podcast, $podcast->getPrevious()['cover_image_path'] ?? null);
        }
    }

    public function deleted(Podcast $podcast): void
    {
        $this->lifecycle->deleted($podcast, 'cover_image_path');
        $this->deleteOriginalAfterCommit($podcast, $podcast->cover_image_path);
    }

    private function deleteOriginalAfterCommit(Podcast $podcast, mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        $podcast->getConnection()
            ->afterCommit(function () use ($path): void {
                Storage::disk('public')->delete($path);
            });
    }
}
