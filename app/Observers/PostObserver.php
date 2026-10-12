<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use JeffreyDavidson\CreatorKit\Services\Media\StoredMediaLifecycle;

class PostObserver
{
    public function __construct(private readonly StoredMediaLifecycle $lifecycle) {}

    public function created(Post $post): void
    {
        $this->lifecycle->created($post, 'featured_image_path', 'post');
    }

    public function updated(Post $post): void
    {
        $this->lifecycle->updated($post, 'featured_image_path', 'post');
    }

    public function forceDeleted(Post $post): void
    {
        $this->lifecycle->forceDeleted($post, 'featured_image_path', 'post');
    }
}
