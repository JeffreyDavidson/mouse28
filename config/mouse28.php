<?php

return [
    'production_url' => env('MOUSE28_PRODUCTION_URL', 'https://mouse28.com'),

    'production_sync' => [
        'ssh_host' => env('MOUSE28_PRODUCTION_SSH_HOST', 'cold-moon'),
        'site_path' => env('MOUSE28_PRODUCTION_SITE_PATH', '/home/forge/mouse28.com/current'),
    ],

    'content_artwork_path' => resource_path('content-artwork'),

    'guides_enabled' => env('GUIDES_ENABLED', false),

    'guide_review_interval_days' => (int) env('GUIDE_REVIEW_INTERVAL_DAYS', 180),

    'post_review_interval_days' => (int) env('POST_REVIEW_INTERVAL_DAYS', 180),

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Mouse28 Administrator'),
        'email' => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
];
