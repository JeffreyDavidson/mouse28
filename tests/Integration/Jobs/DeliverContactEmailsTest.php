<?php

use App\Jobs\DeliverContactEmails;
use App\Jobs\SendContactMessageEmails;

test('previously serialized contact jobs retain the message and delivery settings', function (): void {
    $payload = 'O:29:"App\\Jobs\\DeliverContactEmails":1:{s:16:"contactMessageId";i:42;}';

    $job = unserialize($payload, ['allowed_classes' => [DeliverContactEmails::class]]);

    if (! $job instanceof SendContactMessageEmails) {
        throw new UnexpectedValueException('The legacy payload did not resolve to the contact email job.');
    }

    expect($job->contactMessageId)->toBe(42)
        ->and($job->tries)->toBe(3)
        ->and($job->timeout)->toBe(60);
});
