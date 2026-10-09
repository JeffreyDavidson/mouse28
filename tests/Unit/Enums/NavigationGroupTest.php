<?php

use App\Enums\NavigationGroup;

test('navigation groups list the admin menu sections in order with their labels', function (): void {
    $labels = [];

    foreach (NavigationGroup::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'content' => 'Content',
        'communication' => 'Communication',
        'settings' => 'Settings',
    ]);
});
