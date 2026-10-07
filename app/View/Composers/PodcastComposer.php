<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Support\PodcastLinks;
use App\Support\PrimaryPodcast;
use Illuminate\View\View;

class PodcastComposer
{
    public function __construct(private readonly PrimaryPodcast $primaryPodcast) {}

    public function compose(View $view): void
    {
        $podcast = $this->primaryPodcast->current();

        $view->with([
            'podcast' => $podcast,
            'podcastLinks' => PodcastLinks::for($podcast),
        ]);
    }
}
