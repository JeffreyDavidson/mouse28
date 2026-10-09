<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Support\Facades\Config;

class PodcastLinks
{
    /**
     * The show's listening links, with the episode's own YouTube video in place of the channel when it has one.
     *
     * @return list<array{label: string, url: string, description: string}>
     */
    public static function for(Podcast $podcast, ?Episode $episode = null): array
    {
        $youtubeUrl = $episode?->youtube_url ?: $podcast->youtube_url;

        return array_values(array_filter([
            $podcast->apple_url ? ['label' => 'Apple Podcasts', 'url' => $podcast->apple_url, 'description' => 'Visit the show'] : null,
            $podcast->spotify_url ? ['label' => 'Spotify', 'url' => $podcast->spotify_url, 'description' => 'Visit the show'] : null,
            $youtubeUrl ? [
                'label' => 'YouTube',
                'url' => $youtubeUrl,
                'description' => $episode?->youtube_url ? 'Watch this episode' : 'Visit the channel',
            ] : null,
            ['label' => 'RSS Feed', 'url' => Config::string('podcast.rss_url'), 'description' => 'Subscribe in another podcast app'],
        ]));
    }
}
