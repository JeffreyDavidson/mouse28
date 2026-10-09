<?php

use App\Enums\SearchContentType;

test('search content types keep their page order and group keys', function (): void {
    expect(array_column(SearchContentType::cases(), 'value'))->toBe(['posts', 'guides', 'episodes']);
});

test('search content types label their result sections', function (): void {
    expect(SearchContentType::labels())->toBe([
        'posts' => 'Blog posts',
        'guides' => 'Guides',
        'episodes' => 'Podcast episodes',
    ]);
});
