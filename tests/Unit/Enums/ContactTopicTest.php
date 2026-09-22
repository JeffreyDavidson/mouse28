<?php

use App\Enums\ContactTopic;

test('contact topics expose admin labels', function (): void {
    $labels = [];

    foreach (ContactTopic::cases() as $case) {
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

test('contact topics expose contact form labels', function (): void {
    $labels = [];

    foreach (ContactTopic::cases() as $case) {
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
