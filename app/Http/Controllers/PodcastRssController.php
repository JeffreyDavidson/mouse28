<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;

class PodcastRssController
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->away(Config::string('podcast.rss_url'), 301);
    }
}
