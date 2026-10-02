<?php

use App\Enums\SocialPlatform;

test('social platforms expose explicit stored values and labels', function (): void {
    $details = [];

    foreach (SocialPlatform::cases() as $case) {
        $details[$case->value] = $case->getLabel();
    }

    expect($details)->toBe([
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'x' => 'X / Twitter',
        'bluesky' => 'Bluesky',
        'other' => 'Other',
    ]);
});
