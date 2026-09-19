<?php

use App\Enums\ContentAuthor;

test('content authors expose explicit labels for every case', function (): void {
    $labels = [];

    foreach (ContentAuthor::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'jeffrey' => 'Jeffrey Davidson',
        'cassie' => 'Cassie Davidson',
        'both' => 'Jeffrey & Cassie',
    ]);
});
