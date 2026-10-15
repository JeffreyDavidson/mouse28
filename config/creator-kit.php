<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;

/*
 * Mouse28's settings for the shared creator-kit package. Laravel merges this file over the
 * package's defaults one top-level key at a time, so a key set here must be complete.
 */
return [
    'media' => [
        /*
         * The widths, in pixels, of the responsive WebP variants generated for
         * every stored image. Keep the list ascending.
         */
        'responsive_widths' => [480, 640, 768, 1280],

        /*
         * The image columns media:verify-responsive-images and media:repair-responsive-images
         * cover, in report order. Episode covers are shown in square frames.
         */
        'responsive_images' => [
            ['model' => Post::class, 'column' => 'featured_image_path', 'label' => 'post'],
            ['model' => Episode::class, 'column' => 'featured_image_path', 'label' => 'episode', 'square' => true],
            ['model' => Guide::class, 'column' => 'featured_image_path', 'label' => 'guide'],
            ['model' => Podcast::class, 'column' => 'cover_image_path', 'label' => 'podcast'],
        ],
    ],

    'newsletter' => [
        'models' => [
            'subscriber' => Subscriber::class,
            'issue' => NewsletterIssue::class,
            'delivery' => NewsletterDelivery::class,
        ],

        /*
         * An editor's test send goes to the admin addresses (MAIL_ADMIN_ADDRESS, comma separated),
         * the same list mail.admin_address holds.
         */
        'test_recipients' => array_values(array_filter(array_map(trim(...), explode(',', (string) env('MAIL_ADMIN_ADDRESS', 'hello@example.com'))))),

        /*
         * An unusable confirmation link goes back to the home page's sign-up form, with its
         * message in the form's own error bag.
         */
        'signup_form' => [
            'route' => 'home',
            'fragment' => 'newsletter',
            'error_bag' => 'newsletter',
        ],
    ],
];
