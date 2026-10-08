<?php

use App\Enums\ContactType;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

test('received contact email uses accessible current branding without external fonts', function (): void {
    $inquiry = ContactInquiry::factory()->make();
    $inquiry->id = 1;
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
    $inquiry->id = 1;
    $inquiry->created_at = now();

    $html = new ContactMessageReceived($inquiry)->render();

    expect($html)->toContain('Private Sender', 'private@example.test', 'Confidential message', 'Podcast Guest')
        ->toContain('href="mailto:private@example.test?subject=Re%3A%20Podcast%20Guest"');
});

test('received contact email uses the readable type label and replies to the sender', function (ContactType $type, string $label): void {
    $inquiry = ContactInquiry::factory()->make(['name' => 'Dale Cooper', 'email' => 'dale@example.com', 'type' => $type]);

    $envelope = new ContactMessageReceived($inquiry)->envelope();

    expect($envelope->subject)->toBe("New Contact: {$label}")
        ->and($envelope->replyTo)
        ->toEqual([new Address('dale@example.com', 'Dale Cooper')]);
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

    $headers = new ContactMessageReceived($inquiry)->headers()
        ->text;

    expect($headers['Resend-Idempotency-Key'])->toBe('mouse28-contact-'.hash('sha256', "https://mouse28.test|7|{$createdAt->toISOString()}").'-notification');
});

test('received contact email shows when it arrived in Eastern time and links to the inquiry in the admin', function (): void {
    $inquiry = ContactInquiry::factory()->make();
    $inquiry->id = 42;
    $inquiry->created_at = Date::parse('2026-10-07 01:30:00', 'UTC');

    $html = new ContactMessageReceived($inquiry)->render();

    expect($html)->toContain('Oct 6, 2026', '9:30 PM')
        ->not->toContain('Oct 7, 2026', '1:30 AM')
        ->toContain('href="'.ContactInquiryResource::getUrl('view', ['record' => $inquiry]).'"');
});

test('received contact email html matches its snapshot', function (): void {
    URL::forceRootUrl('https://mouse28.test');
    URL::forceScheme('https');
    $inquiry = ContactInquiry::factory()->make([
        'name' => 'Dale Cooper',
        'email' => 'dale@example.test',
        'type' => ContactType::Accessibility,
        'message' => "Line one & <two>\nLine three",
    ]);
    $inquiry->id = 42;
    $inquiry->created_at = Date::parse('2026-10-07 01:30:00', 'UTC');

    $html = new ContactMessageReceived($inquiry)->render();

    // Indentation and blank lines are not compared, so moving markup into a layout keeps the snapshot.
    $lines = array_filter(array_map(trim(...), explode("\n", $html)), fn (string $line): bool => $line !== '');

    expect(implode("\n", $lines))->toMatchSnapshot();
});
