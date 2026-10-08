<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\PodcastLinks;

covers(PodcastLinks::class);

test('links include configured services and the RSS feed', function (): void {
    config()->set('podcast.rss_url', 'https://mouse28.test/podcast.xml');
    $podcast = new Podcast([
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => null,
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);

    $links = PodcastLinks::for($podcast);

    expect($links)->toBe([
        ['label' => 'Apple Podcasts', 'url' => 'https://podcasts.apple.com/show/mouse28', 'description' => 'Visit the show'],
        ['label' => 'YouTube', 'url' => 'https://youtube.com/@mouse28', 'description' => 'Visit the channel'],
        ['label' => 'RSS Feed', 'url' => 'https://mouse28.test/podcast.xml', 'description' => 'Subscribe in another podcast app'],
    ]);
});

test('an episode video replaces the channel link', function (): void {
    config()->set('podcast.rss_url', 'https://mouse28.test/podcast.xml');
    $podcast = new Podcast([
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);
    $episode = new Episode(['youtube_url' => 'https://youtube.com/watch?v=1']);

    $links = PodcastLinks::for($podcast, $episode);

    expect($links)->toBe([
        ['label' => 'Spotify', 'url' => 'https://open.spotify.com/show/mouse28', 'description' => 'Visit the show'],
        ['label' => 'YouTube', 'url' => 'https://youtube.com/watch?v=1', 'description' => 'Watch this episode'],
        ['label' => 'RSS Feed', 'url' => 'https://mouse28.test/podcast.xml', 'description' => 'Subscribe in another podcast app'],
    ]);
});

test('an episode without a video keeps the channel link', function (): void {
    $podcast = new Podcast(['youtube_url' => 'https://youtube.com/@mouse28']);

    $links = PodcastLinks::for($podcast, new Episode);

    expect($links[0])->toBe(['label' => 'YouTube', 'url' => 'https://youtube.com/@mouse28', 'description' => 'Visit the channel']);
});

test('an episode video is listed even when the show has no channel', function (): void {
    $episode = new Episode(['youtube_url' => 'https://youtube.com/watch?v=1']);

    $links = PodcastLinks::for(new Podcast, $episode);

    expect($links[0])->toBe(['label' => 'YouTube', 'url' => 'https://youtube.com/watch?v=1', 'description' => 'Watch this episode']);
});
