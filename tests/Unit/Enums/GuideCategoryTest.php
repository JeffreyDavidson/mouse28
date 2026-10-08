<?php

use App\Enums\GuideCategory;

test('guide categories expose explicit labels for every case', function (): void {
    $labels = [];

    foreach (GuideCategory::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'accessibility' => 'Accessibility',
        'park-strategy' => 'Park Strategy',
        'food-reviews' => 'Food & Reviews',
        'family-planning' => 'Family Planning',
    ]);
});

test('guide categories each have their bundled artwork', function (): void {
    $artwork = [];

    foreach (GuideCategory::cases() as $case) {
        $artwork[$case->value] = $case->artworkUrl();
    }

    expect($artwork)->toBe([
        'accessibility' => '/images/guides/accessibility.webp',
        'park-strategy' => '/images/guides/park-strategy.webp',
        'food-reviews' => '/images/guides/food-reviews.webp',
        'family-planning' => '/images/guides/family-planning.webp',
    ]);
});
