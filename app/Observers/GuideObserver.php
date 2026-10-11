<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Guide;
use JeffreyDavidson\CreatorKit\Services\Media\StoredMediaLifecycle;

class GuideObserver
{
    public function __construct(private readonly StoredMediaLifecycle $lifecycle) {}

    public function created(Guide $guide): void
    {
        $this->lifecycle->created($guide, 'featured_image_path', 'guide');
    }

    public function updated(Guide $guide): void
    {
        $this->lifecycle->updated($guide, 'featured_image_path', 'guide');
    }

    public function forceDeleted(Guide $guide): void
    {
        $this->lifecycle->forceDeleted($guide, 'featured_image_path', 'guide');
    }
}
