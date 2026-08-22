<?php

namespace App\View\Composers;

use App\Models\Podcast;
use Illuminate\View\View;

class PodcastComposer
{
    public function compose(View $view): void
    {
        $view->with('podcast', Podcast::info());
    }
}
