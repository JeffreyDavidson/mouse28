<?php

use App\Enums\PublicationStatus;

test('publication statuses expose explicit labels', function (): void {
    $labels = [];

    foreach (PublicationStatus::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'draft' => 'Draft',
        'needs-publish-date' => 'Needs publish date',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
    ]);
});

test('publication statuses expose explicit colors', function (): void {
    $colors = [];

    foreach (PublicationStatus::cases() as $case) {
        $colors[$case->value] = $case->getColor();
    }

    expect($colors)->toBe([
        'draft' => 'gray',
        'needs-publish-date' => 'warning',
        'scheduled' => 'warning',
        'published' => 'success',
    ]);
});
