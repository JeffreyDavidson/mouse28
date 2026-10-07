<?php

return [
    'rss_url' => env('PODCAST_RSS_URL', 'https://feeds.transistor.fm/mouse28'),

    // Used for the site's one show until a podcast row exists (App\Support\PrimaryPodcast).
    'default_show' => [
        'name' => 'Mouse28',
        'slug' => 'mouse28',
        'description' => 'Disney parks through the lens of raising a daughter with autism.',
    ],
];
