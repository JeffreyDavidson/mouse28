<?php

use App\Models\Concerns\HasOgImage;
use App\Models\Post;

covers(HasOgImage::class);

test('a stored social image path resolves to its public storage URL', function (?string $path, ?string $url): void {
    expect((new Post(['og_image' => $path]))->og_image_url)->toBe($url);
})->with([
    'a stored image' => ['social/a.jpg', '/storage/social/a.jpg'],
    'an empty path' => ['', null],
    'no path' => [null, null],
]);
