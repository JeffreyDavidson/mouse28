<?php

return [
    'guide_review_interval_days' => (int) env('GUIDE_REVIEW_INTERVAL_DAYS', 180),

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Mouse28 Administrator'),
        'email' => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],
];
