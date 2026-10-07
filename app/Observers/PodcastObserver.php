<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Services\ResponsiveImageLifecycle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Removes a replaced podcast cover after commit, and a deleted podcast's cover only when
 * it is permanently deleted, so a trashed podcast can be restored with its cover. Also
 * restores the episodes that were trashed together with the podcast.
 */
class PodcastObserver
{
    public function __construct(private readonly ResponsiveImageLifecycle $lifecycle) {}

    /** Until the sluggable package arrives (Phase 6 slice 8), a podcast created without a slug is slugged from its name. */
    public function creating(Podcast $podcast): void
    {
        if (blank($podcast->slug)) {
            $podcast->slug = Str::slug((string) $podcast->name);
        }
    }

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

    /**
     * Restore the episodes that were trashed together with the podcast, leaving episodes
     * that were trashed separately before it in the trash.
     */
    public function restoring(Podcast $podcast): void
    {
        $deletedAt = $podcast->getAttribute('deleted_at');

        if ($deletedAt === null) {
            return;
        }

        $podcast->episodes()
            ->onlyTrashed()
            ->where('deleted_at', '>=', $deletedAt)
            ->each(fn (Episode $episode): bool => $episode->restore());
    }

    public function forceDeleted(Podcast $podcast): void
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
