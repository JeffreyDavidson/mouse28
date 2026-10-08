<?php

use App\Jobs\SendContactInquiryEmails;
use App\Jobs\SendContactMessageEmails;
use Illuminate\Support\Facades\Bus;

covers(SendContactMessageEmails::class);

test('legacy contact-mail jobs forward their preserved ID to the inquiry email job', function (): void {
    Bus::fake([SendContactInquiryEmails::class]);

    new SendContactMessageEmails(42)->handle();

    Bus::assertDispatched(SendContactInquiryEmails::class, fn (SendContactInquiryEmails $job): bool => $job->contactInquiryId === 42);
});

test('legacy contact-mail jobs queued before the release still unserialize', function (): void {
    // The command exactly as the previous release serialized it on the contact-mail queue.
    $legacyCommand = 'O:33:"App\Jobs\SendContactMessageEmails":3:{s:16:"contactMessageId";i:42;s:10:"connection";s:8:"database";s:5:"queue";s:12:"contact-mail";}';

    $job = unserialize($legacyCommand);

    if (! $job instanceof SendContactMessageEmails) {
        throw new UnexpectedValueException('The legacy contact job did not unserialize.');
    }

    expect($job->contactMessageId)->toBe(42)
        ->and($job->queue)
        ->toBe('contact-mail');
});
