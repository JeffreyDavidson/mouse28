<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;

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
];
