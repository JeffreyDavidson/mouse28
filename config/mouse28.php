<?php

return [
    'production_url' => env('MOUSE28_PRODUCTION_URL', 'https://mouse28.com'),

    'deployment_environment' => env('MOUSE28_DEPLOYMENT_ENVIRONMENT', 'production'),

    'production_sync' => [
        'ssh_host' => env('MOUSE28_PRODUCTION_SSH_HOST', 'cold-moon'),
        'site_path' => env('MOUSE28_PRODUCTION_SITE_PATH', '/home/forge/mouse28.com/current'),
    ],

    'content_artwork_path' => resource_path('content-artwork'),

    'guides_enabled' => filter_var(env('GUIDES_ENABLED', false), FILTER_VALIDATE_BOOL),

    'blog_posts_per_page' => max(1, (int) env('MOUSE28_BLOG_POSTS_PER_PAGE', 12)),

    'episodes_per_page' => max(1, (int) env('MOUSE28_EPISODES_PER_PAGE', 12)),

    'guides_per_page' => max(1, (int) env('MOUSE28_GUIDES_PER_PAGE', 12)),

    'search_results_per_page' => max(1, (int) env('MOUSE28_SEARCH_RESULTS_PER_PAGE', 6)),

    'preview_link_hours' => max(1, (int) env('MOUSE28_PREVIEW_LINK_HOURS', 24)),

    'rate_limits' => [
        'contact_form_per_minute' => max(1, (int) env('MOUSE28_CONTACT_FORM_RATE_LIMIT', 5)),
        'newsletter_per_minute' => max(1, (int) env('MOUSE28_NEWSLETTER_RATE_LIMIT', 5)),
        'search_per_minute' => max(1, (int) env('MOUSE28_SEARCH_RATE_LIMIT', 30)),
    ],

    'contact' => [
        'email' => env('MOUSE28_CONTACT_EMAIL', 'hello@example.com'),
    ],

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Mouse28 Administrator'),
        'email' => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
];
