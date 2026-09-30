<?php

use App\Models\Concerns\HasCoverImages;
use App\Models\Post;

covers(HasCoverImages::class);

test('stored image paths resolve to public storage URLs', function (): void {
    $post = new Post(['cover_image' => 'covers/a.jpg', 'og_image' => '']);

    expect($post->cover_image_url)->toBe('/storage/covers/a.jpg')
        ->and($post->og_image_url)->toBeNull();
});
