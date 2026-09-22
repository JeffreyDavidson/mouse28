<?php

use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;

test('submitted contact email uses accessible current branding without external fonts', function (): void {
    $message = new ContactMessage([
        'name' => 'Sample Visitor',
        'email' => 'visitor@example.com',
        'subject' => 'general',
        'message' => 'A sample question.',
    ]);
    $message->created_at = now();

    $html = new ContactFormSubmitted($message)->render();

    expect($html)->toContain('Besley', '<html lang="en">', 'role="presentation"')
        ->not->toContain('Playfair', 'fonts.googleapis.com');
});

test('contact mail uses readable subjects for recognized topics and free text', function (string $subject, string $label): void {
    $message = new ContactMessage(['subject' => $subject, 'email' => 'dale@example.com']);

    $envelope = new ContactFormSubmitted($message)->envelope();

    expect($envelope->subject)->toBe("New Contact: {$label}");
})->with([
    'general' => ['general', 'General Question'],
    'accessibility' => ['accessibility', 'Park Accessibility'],
    'collaboration' => ['collaboration', 'Collaboration / Sponsorship'],
    'guest' => ['guest', 'Podcast Guest'],
    'other' => ['other', 'Other'],
    'free text' => ['Need help with Mouse28', 'Need help with Mouse28'],
]);
