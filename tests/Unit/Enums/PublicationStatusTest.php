<?php

use App\Enums\PublicationStatus;

test('publication statuses expose explicit labels and colors', function (): void {
    $labels = [];
    $colors = [];

    foreach (PublicationStatus::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
        $colors[$case->value] = $case->getColor();
    }

    expect($labels)->toBe([
        'draft' => 'Draft',
        'needs-publish-date' => 'Needs publish date',
        'scheduled' => 'Scheduled',
        'published' => 'Published',
    ])->and($colors)->toBe([
        'draft' => 'gray',
        'needs-publish-date' => 'warning',
        'scheduled' => 'warning',
        'published' => 'success',
    ]);
});
