<?php

use App\Enums\ContactTopic;

test('contact topics expose separate admin and form labels', function (): void {
    $labels = [];
    $formLabels = [];

    foreach (ContactTopic::cases() as $case) {
        $labels[$case->value] = $case->getLabel();
        $formLabels[$case->value] = $case->contactFormLabel();
    }

    expect($labels)->toBe([
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Podcast Guest',
        'other' => 'Other',
    ])->and($formLabels)->toBe([
        'general' => 'General Question',
        'accessibility' => 'Park Accessibility Question',
        'collaboration' => 'Collaboration / Sponsorship',
        'guest' => 'Guest on the Podcast',
        'other' => 'Other',
    ]);
});
