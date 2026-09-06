<?php

use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('contact subjects retain their stored value and readable mail label', function (string $subject, string $label): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.com',
        'subject' => $subject,
        'message' => 'A question about visiting the parks.',
    ]);

    $message->refresh();

    expect($message->subject)->toBe($subject)
        ->and($message->subject_label)->toBe($label)
        ->and((new ContactFormSubmitted($message))->envelope()->subject)->toBe("New Contact: {$label}");
})->with([
    'general' => ['general', 'General Question'],
    'accessibility' => ['accessibility', 'Park Accessibility'],
    'collaboration' => ['collaboration', 'Collaboration / Sponsorship'],
    'guest' => ['guest', 'Podcast Guest'],
    'other' => ['other', 'Other'],
    'free text' => ['Need help with Mouse28', 'Need help with Mouse28'],
]);
