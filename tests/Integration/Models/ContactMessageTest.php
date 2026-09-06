<?php

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
