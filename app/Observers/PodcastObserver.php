<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Services\Media\StoredMediaLifecycle;

/**
 * Keeps the podcast cover's files in step through creator-kit's StoredMediaLifecycle: a
 * replaced cover is removed after commit, and a deleted podcast's cover only when it is
 * permanently deleted, so a trashed podcast can be restored with its cover. Also restores
 * the episodes that were trashed together with the podcast.
 */
class PodcastObserver
{
    public function __construct(private readonly StoredMediaLifecycle $lifecycle) {}

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
        $this->lifecycle->forceDeleted($podcast, 'cover_image_path', 'podcast');
    }
}
