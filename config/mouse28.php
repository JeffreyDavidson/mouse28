<?php

return [
    'production_url' => env('MOUSE28_PRODUCTION_URL', 'https://mouse28.com'),

    'production_sync' => [
        'ssh_host' => env('MOUSE28_PRODUCTION_SSH_HOST', 'cold-moon'),
        'site_path' => env('MOUSE28_PRODUCTION_SITE_PATH', '/home/forge/mouse28.com/current'),
    ],

    'content_artwork_path' => resource_path('content-artwork'),

    'guides_enabled' => filter_var(env('GUIDES_ENABLED', false), FILTER_VALIDATE_BOOL),

    'blog_posts_per_page' => max(1, (int) env('MOUSE28_BLOG_POSTS_PER_PAGE', 12)),

    'episodes_per_page' => max(1, (int) env('MOUSE28_EPISODES_PER_PAGE', 12)),

    'guides_per_page' => max(1, (int) env('MOUSE28_GUIDES_PER_PAGE', 12)),

    'newsletter_issues_per_page' => max(1, (int) env('MOUSE28_NEWSLETTER_ISSUES_PER_PAGE', 12)),

    'newsletter_feed_items' => max(1, (int) env('MOUSE28_NEWSLETTER_FEED_ITEMS', 20)),

    'search_results_per_page' => max(1, (int) env('MOUSE28_SEARCH_RESULTS_PER_PAGE', 6)),

    'preview_link_hours' => max(1, (int) env('MOUSE28_PREVIEW_LINK_HOURS', 24)),

    'rate_limits' => [
        'contact_form_per_minute' => max(1, (int) env('MOUSE28_CONTACT_FORM_RATE_LIMIT', 5)),
        'newsletter_per_minute' => max(1, (int) env('MOUSE28_NEWSLETTER_RATE_LIMIT', 5)),
        'newsletter_delivery_per_second' => max(1, (int) env('MOUSE28_NEWSLETTER_DELIVERY_RATE_LIMIT', 5)),
        'newsletter_confirm_per_minute' => max(1, (int) env('MOUSE28_NEWSLETTER_CONFIRM_RATE_LIMIT', 10)),
        // Mail providers send one-click unsubscribes from a few shared addresses, often in a burst after a send.
        'newsletter_unsubscribe_per_minute' => max(1, (int) env('MOUSE28_NEWSLETTER_UNSUBSCRIBE_RATE_LIMIT', 120)),
        'resend_webhook_per_minute' => max(1, (int) env('MOUSE28_RESEND_WEBHOOK_RATE_LIMIT', 60)),
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
    // Categories are admin-editable, so the homepage planning list and the post artwork styles are keyed by slug here.
    'home_planning_category_slugs' => ['park-accessibility', 'disney-tips', 'autism-awareness'],

    // A post in a category without a style here uses `general`. resources/css/app.css scans this file for the classes.
    'post_artwork_styles' => [
        'disney-tips' => ['wash' => 'from-gold/35 via-cream to-purple/15', 'ink' => 'text-navy', 'stamp' => 'Planning note'],
        'park-accessibility' => ['wash' => 'from-purple/30 via-cream to-gold/20', 'ink' => 'text-purple-dark', 'stamp' => 'Access field note'],
        'episode-recap' => ['wash' => 'from-emerald-800/25 via-cream to-gold/20', 'ink' => 'text-emerald-900', 'stamp' => 'From the podcast'],
        'family-life' => ['wash' => 'from-blue-800/25 via-cream to-gold/25', 'ink' => 'text-blue-950', 'stamp' => 'Family dispatch'],
        'autism-awareness' => ['wash' => 'from-pink-800/20 via-cream to-purple/20', 'ink' => 'text-pink-950', 'stamp' => 'Different perspectives'],
        'disney-news' => ['wash' => 'from-orange-700/25 via-cream to-purple/15', 'ink' => 'text-orange-950', 'stamp' => 'Park bulletin'],
        'food-reviews' => ['wash' => 'from-amber-800/25 via-cream to-gold/25', 'ink' => 'text-amber-950', 'stamp' => 'Table notes'],
        'resort-reviews' => ['wash' => 'from-teal-800/25 via-cream to-gold/20', 'ink' => 'text-teal-950', 'stamp' => 'Resort field note'],
        'disney-plus' => ['wash' => 'from-indigo-800/25 via-cream to-purple/20', 'ink' => 'text-indigo-950', 'stamp' => 'Watch list'],
        'merchandise' => ['wash' => 'from-rose-800/20 via-cream to-gold/25', 'ink' => 'text-rose-950', 'stamp' => 'Things we found'],
        'general' => ['wash' => 'from-cyan-900/20 via-cream to-gold/20', 'ink' => 'text-navy', 'stamp' => 'Mouse28 dispatch'],
    ],
];
