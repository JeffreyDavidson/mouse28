<?php

use App\Models\Subscriber;
use JeffreyDavidson\CreatorKit\Enums\SubscriberStatus;

test('a suppressed subscriber is suppressed even though it is also unsubscribed', function (): void {
    $subscriber = new Subscriber(['verified_at' => '2026-09-01', 'unsubscribed_at' => '2026-09-10', 'suppressed_at' => '2026-09-10']);

    expect($subscriber->status())->toBe(SubscriberStatus::Suppressed);
});

test('a subscriber status follows the confirmation and unsubscribe dates', function (?string $verifiedAt, ?string $unsubscribedAt, SubscriberStatus $expected): void {
    $subscriber = new Subscriber(['verified_at' => $verifiedAt, 'unsubscribed_at' => $unsubscribedAt]);

    expect($subscriber->status())->toBe($expected);
})->with([
    'confirmed' => ['2026-09-01', null, SubscriberStatus::Active],
    'never confirmed' => [null, null, SubscriberStatus::Pending],
    'confirmed then unsubscribed' => ['2026-09-01', '2026-09-10', SubscriberStatus::Unsubscribed],
    'unsubscribed before confirming' => [null, '2026-09-10', SubscriberStatus::Unsubscribed],
]);
