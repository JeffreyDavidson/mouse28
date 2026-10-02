<?php

use App\Enums\ContactType;

test('contact types expose admin labels', function (): void {
    $labels = [];

    foreach (ContactType::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
    }

    expect($labels)->toBe([
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Podcast Guest',
        'other' => 'Other',
    ]);
});

test('contact types expose contact form labels', function (): void {
    $labels = [];

    foreach (ContactType::cases() as $case) {
        $labels[$case->value] = $case->contactFormLabel();
    }

    expect($labels)->toBe([
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility Question',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Guest on the Podcast',
        'other' => 'Other',
    ]);
});
