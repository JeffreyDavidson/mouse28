<?php

use App\Jobs\DeliverContactEmails;
use App\Jobs\SendContactMessageEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\AssertableJsonString;

pest()->use(RefreshDatabase::class);

test('previously serialized contact jobs retain the message and delivery settings', function (): void {
    $payload = 'O:29:"App\\Jobs\\DeliverContactEmails":1:{s:16:"contactMessageId";i:42;}';

    $job = unserialize($payload, ['allowed_classes' => [DeliverContactEmails::class]]);

    if (! $job instanceof SendContactMessageEmails) {
        throw new UnexpectedValueException('The legacy payload did not resolve to the contact email job.');
    }

    $job->beforeCommit();

    Bus::dispatch($job);
    $queuePayload = DB::table('jobs')->where('queue', 'contact-mail')->value('payload');

    if (! is_string($queuePayload)) {
        throw new UnexpectedValueException('The legacy job was not stored on its database queue.');
    }

    expect($job->contactMessageId)->toBe(42);
    new AssertableJsonString($queuePayload)
        ->assertPath('maxTries', 3)
        ->assertPath('timeout', 60)
        ->assertPath('failOnTimeout', true)
        ->assertPath('backoff', '60,300,900');
});
