<?php

use App\Models\Podcast;
use App\Support\PodcastLinks;

test('links include configured services and the RSS feed', function (): void {
    config()->set('podcast.rss_url', 'https://mouse28.test/podcast.xml');
    $podcast = new Podcast([
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => null,
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);

    $links = PodcastLinks::for($podcast);

    expect($links)->toBe([
        ['label' => 'Apple Podcasts', 'url' => 'https://podcasts.apple.com/show/mouse28'],
        ['label' => 'YouTube', 'url' => 'https://youtube.com/@mouse28'],
        ['label' => 'RSS Feed', 'url' => 'https://mouse28.test/podcast.xml'],
    ]);
});
