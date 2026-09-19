<?php

use App\Enums\PostCategory;

test('post categories expose explicit labels for every case', function (): void {
    $labels = [];

    foreach (PostCategory::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'disney-tips' => 'Disney Tips',
        'park-accessibility' => 'Park Accessibility',
        'episode-recap' => 'Episode Recap',
        'family-life' => 'Family Life',
        'autism-awareness' => 'Autism Awareness',
        'disney-news' => 'Disney News',
        'food-reviews' => 'Food Reviews',
        'resort-reviews' => 'Resort Reviews',
        'disney-plus' => 'Disney+',
        'merchandise' => 'Merchandise',
        'general' => 'General',
    ]);
});

test('post category colors are explicit for every case', function (): void {
    $colors = [];

    foreach (PostCategory::cases() as $case) {
        $colors[$case->value] = $case->getColor();
    }

    expect($colors)->toBe([
        'disney-tips' => 'info',
        'park-accessibility' => 'success',
        'episode-recap' => 'warning',
        'family-life' => 'danger',
        'autism-awareness' => 'primary',
        'disney-news' => 'gray',
        'food-reviews' => 'gray',
        'resort-reviews' => 'gray',
        'disney-plus' => 'gray',
        'merchandise' => 'gray',
        'general' => 'gray',
    ]);
});
