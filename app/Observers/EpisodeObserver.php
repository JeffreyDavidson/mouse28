<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Episode;
use App\Services\ResponsiveImageLifecycle;
use App\Services\SquareResponsiveImageVariants;
use App\Support\PrimaryPodcast;

/**
 * Episode covers are shown in square frames, so their variants are square crops.
 */
class EpisodeObserver
{
    private readonly ResponsiveImageLifecycle $lifecycle;

    public function __construct(SquareResponsiveImageVariants $images, private readonly PrimaryPodcast $primaryPodcast)
    {
        $this->lifecycle = new ResponsiveImageLifecycle($images);
    }

    /** Mouse28 has one show, so an episode created without a podcast joins it. */
    public function creating(Episode $episode): void
    {
        if ($episode->podcast_id === null) {
            $episode->podcast()->associate($this->primaryPodcast->findOrCreate());
        }
    }

    public function created(Episode $episode): void
    {
        $this->lifecycle->created($episode, 'featured_image_path', 'episode');
    }

    public function updated(Episode $episode): void
    {
        $this->lifecycle->updated($episode, 'featured_image_path', 'episode');
    }

    public function forceDeleted(Episode $episode): void
    {
        $this->lifecycle->deleted($episode, 'featured_image_path');
    }
}
