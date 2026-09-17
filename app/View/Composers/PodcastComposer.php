<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Podcast;
use App\Support\PodcastLinks;
use Illuminate\View\View;

class PodcastComposer
{
    public function compose(View $view): void
    {
        $podcast = Podcast::info();

        $view->with([
            'podcast' => $podcast,
            'podcastLinks' => PodcastLinks::for($podcast),
        ]);
    }
}
