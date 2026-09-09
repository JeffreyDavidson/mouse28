<?php

namespace App\Support;

use App\Models\Podcast;
use Illuminate\Support\Facades\Config;

class PodcastLinks
{
    /** @return list<array{label: string, url: string}> */
    public static function for(Podcast $podcast): array
    {
        return array_values(array_filter([
            $podcast->apple_url ? ['label' => 'Apple Podcasts', 'url' => $podcast->apple_url] : null,
            $podcast->spotify_url ? ['label' => 'Spotify', 'url' => $podcast->spotify_url] : null,
            $podcast->youtube_url ? ['label' => 'YouTube', 'url' => $podcast->youtube_url] : null,
            ['label' => 'RSS Feed', 'url' => Config::string('podcast.rss_url')],
        ]));
    }
}
