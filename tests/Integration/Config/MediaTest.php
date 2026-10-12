<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;

test('mouse28 generates responsive variants at 480, 640, 768 and 1280 pixels', function (): void {
    expect(config('creator-kit.media.responsive_widths'))->toBe([480, 640, 768, 1280]);
});

test('the responsive image commands cover posts, square episode covers, guides and the podcast, in that order', function (): void {
    expect(config('creator-kit.media.responsive_images'))->toBe([
        ['model' => Post::class, 'column' => 'featured_image_path', 'label' => 'post'],
        ['model' => Episode::class, 'column' => 'featured_image_path', 'label' => 'episode', 'square' => true],
        ['model' => Guide::class, 'column' => 'featured_image_path', 'label' => 'guide'],
        ['model' => Podcast::class, 'column' => 'cover_image_path', 'label' => 'podcast'],
    ]);
});
