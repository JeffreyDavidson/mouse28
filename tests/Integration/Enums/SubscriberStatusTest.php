<?php

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;

covers(SubscriberStatus::class);

test('a subscriber status follows the confirmation and unsubscribe dates', function (?string $verifiedAt, ?string $unsubscribedAt, SubscriberStatus $expected): void {
    $subscriber = new Subscriber(['verified_at' => $verifiedAt, 'unsubscribed_at' => $unsubscribedAt]);

    expect(SubscriberStatus::for($subscriber))->toBe($expected);
})->with([
    'confirmed' => ['2026-09-01', null, SubscriberStatus::Active],
    'never confirmed' => [null, null, SubscriberStatus::Pending],
    'confirmed then unsubscribed' => ['2026-09-01', '2026-09-10', SubscriberStatus::Unsubscribed],
    'unsubscribed before confirming' => [null, '2026-09-10', SubscriberStatus::Unsubscribed],
]);
