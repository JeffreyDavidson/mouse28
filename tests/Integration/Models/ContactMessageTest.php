<?php

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('contact subjects retain their stored value and readable label', function (string $subject, string $label): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => $subject,
        'message' => 'A question about visiting the parks.',
    ]);

    $message->refresh();

    expect($message->subject)->toBe($subject)
        ->and($message->subjectLabel())->toBe($label);
})->with([
    'general' => ['general', 'General Question'],
    'accessibility' => ['accessibility', 'Park Accessibility'],
    'collaboration' => ['collaboration', 'Collaboration / Sponsorship'],
    'guest' => ['guest', 'Podcast Guest'],
    'other' => ['other', 'Other'],
    'free text' => ['Need help with Mouse28', 'Need help with Mouse28'],
]);

test('reply links percent-encode the address and subject for mail clients', function (string $email, string $subject, string $url): void {
    $message = ContactMessage::query()->make([
        'email' => $email,
        'subject' => $subject,
    ]);

    expect($message->replyMailtoUrl())->toBe($url);
})->with([
    'spaces in the subject' => ['dale@example.com', 'general', 'mailto:dale@example.com?subject=Re%3A%20General%20Question'],
    'query delimiters in the address' => ['dale?cc=x&y@example.com', 'other', 'mailto:dale%3Fcc%3Dx%26y@example.com?subject=Re%3A%20Other'],
]);
