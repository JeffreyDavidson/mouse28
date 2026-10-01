<?php

use App\Enums\SubscriberStatus;
use Filament\Support\Icons\Heroicon;

test('subscriber statuses expose explicit labels colors and icons', function (): void {
    $details = [];

    foreach (SubscriberStatus::cases() as $case) {
        $details[$case->value] = [$case->getLabel(), $case->getColor(), $case->getIcon()];
    }

    expect($details)->toBe([
        'active' => ['Active', 'success', Heroicon::OutlinedCheckCircle],
        'pending' => ['Pending confirmation', 'warning', Heroicon::OutlinedClock],
        'unsubscribed' => ['Unsubscribed', 'gray', Heroicon::OutlinedXCircle],
    ]);
});
