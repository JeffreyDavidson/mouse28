<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class PodcastRssController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->away((string) config('podcast.rss_url'), 301);
    }
}
