<?php

use App\Support\Seo\CollectionListing;
use Illuminate\Pagination\LengthAwarePaginator;

test('a paginated listing maps its items and offsets positions by the earlier pages', function (): void {
    $paginator = new LengthAwarePaginator(['first', 'second'], total: 12, perPage: 5, currentPage: 3);

    $listing = CollectionListing::paginated(
        'Archive',
        'https://example.test/archive?page=3',
        $paginator,
        fn (string $slug): array => ['name' => ucfirst($slug), 'url' => "https://example.test/{$slug}"],
    );

    expect($listing)
        ->name->toBe('Archive')
        ->url->toBe('https://example.test/archive?page=3')
        ->items->toBe([
            ['name' => 'First', 'url' => 'https://example.test/first'],
            ['name' => 'Second', 'url' => 'https://example.test/second'],
        ])
        ->positionOffset->toBe(10);
});
