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
