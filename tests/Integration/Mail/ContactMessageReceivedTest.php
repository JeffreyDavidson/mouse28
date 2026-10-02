<?php

use App\Enums\ContactType;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Date;

test('received contact email uses accessible current branding without external fonts', function (): void {
    $inquiry = ContactInquiry::factory()->make();
    $inquiry->created_at = now();

    $html = new ContactMessageReceived($inquiry)->render();

    expect($html)->toContain('Besley', '<html lang="en">', 'role="presentation"')
        ->not->toContain('Playfair', 'fonts.googleapis.com');
});

test('received contact email renders the inquiry details and an encoded reply link', function (): void {
    $inquiry = ContactInquiry::factory()->make([
        'name' => 'Private Sender',
        'email' => 'private@example.test',
        'type' => ContactType::Guest,
        'message' => 'Confidential message',
    ]);
    $inquiry->created_at = now();

    $html = new ContactMessageReceived($inquiry)->render();

    expect($html)->toContain('Private Sender', 'private@example.test', 'Confidential message', 'Podcast Guest')
        ->toContain('href="mailto:private@example.test?subject=Re%3A%20Podcast%20Guest"');
});

test('received contact email uses the readable type label and replies to the sender', function (ContactType $type, string $label): void {
    $inquiry = ContactInquiry::factory()->make(['name' => 'Dale Cooper', 'email' => 'dale@example.com', 'type' => $type]);

    $envelope = new ContactMessageReceived($inquiry)->envelope();

    expect($envelope->subject)->toBe("New Contact: {$label}")
        ->and($envelope->replyTo)->toEqual([new Address('dale@example.com', 'Dale Cooper')]);
})->with([
    'general' => [ContactType::General, 'General Question'],
    'accessibility' => [ContactType::Accessibility, 'Park Accessibility'],
    'collaboration' => [ContactType::Collaboration, 'Collaboration / Sponsorship'],
    'guest' => [ContactType::Guest, 'Podcast Guest'],
    'other' => [ContactType::Other, 'Other'],
]);

test('received contact email is sent immediately by the delivery job rather than queued', function (): void {
    expect(class_implements(ContactMessageReceived::class))->not->toContain(ShouldQueue::class);
});

test('received contact email carries a mouse28 notification idempotency key', function (): void {
    config()->set('app.url', 'https://mouse28.test');
    $inquiry = ContactInquiry::factory()->make();
    $inquiry->id = 7;
    $createdAt = Date::parse('2026-10-02 12:00:00');
    $inquiry->created_at = $createdAt;

    $headers = new ContactMessageReceived($inquiry)->headers()->text;

    expect($headers['Resend-Idempotency-Key'])->toBe('mouse28-contact-'.hash('sha256', "https://mouse28.test|7|{$createdAt->toISOString()}").'-notification');
});
