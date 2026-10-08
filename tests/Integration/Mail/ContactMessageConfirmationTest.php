<?php

use App\Enums\ContactType;
use App\Mail\ContactMessageConfirmation;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

test('contact confirmation uses configured contact addresses for replies', function (string $configured, array $addresses): void {
    config()->set('mail.admin_address', $configured);
    $inquiry = ContactInquiry::factory()->make(['type' => ContactType::Accessibility]);

    $envelope = new ContactMessageConfirmation($inquiry)->envelope();

    expect($envelope->subject)->toBe('We got your message! — Mouse28')
        ->and($envelope->replyTo)
        ->toEqual($addresses);
})->with([
    'one recipient' => ['hello@mouse28.test', [new Address('hello@mouse28.test')]],
    'multiple recipients with whitespace' => ['hello@mouse28.test, second@mouse28.test, ', [new Address('hello@mouse28.test'), new Address('second@mouse28.test')]],
]);

test('contact confirmation never echoes what the visitor submitted', function (string $name, string $message): void {
    // Arrange
    $inquiry = ContactInquiry::factory()->make([
        'name' => $name,
        'email' => 'visitor@example.com',
        'type' => ContactType::Accessibility,
        'message' => $message,
    ]);
    $mailable = new ContactMessageConfirmation($inquiry);

    // Act
    $subject = $mailable->envelope()
        ->subject;
    $html = $mailable->render();

    // Assert
    expect($subject)->toBe('We got your message! — Mouse28')
        ->and($html)
        ->not->toContain($name, e($name), $message, e($message), 'spam.example', 'Cheap pills', 'Park Accessibility')
        ->and($html)
        ->toContain('Hi there,', 'respond within 48 hours', route('episodes.index'), route('blog.index'))
        ->and($mailable->buildViewData())
        ->not->toHaveKey('inquiry');
})->with([
    'a name carrying a link' => [
        'Claim your prize at https://spam.example/win',
        'Hello',
    ],
    'a message full of links and HTML' => [
        'Jane',
        '<a href="https://spam.example/pills">Cheap pills</a> https://spam.example/offer <script>alert("unsafe")</script>',
    ],
    'markup in the name' => [
        'Dale <Cooper>',
        'Need accessibility help.',
    ],
]);

test('confirmation email uses accessible current branding without external fonts', function (): void {
    $html = new ContactMessageConfirmation(ContactInquiry::factory()->make())->render();

    expect($html)->toContain('Besley', '<html lang="en">', 'role="presentation"')
        ->not->toContain('Playfair', 'fonts.googleapis.com');
});

test('contact confirmation is sent immediately by the delivery job rather than queued', function (): void {
    expect(class_implements(ContactMessageConfirmation::class))->not->toContain(ShouldQueue::class);
});

test('contact confirmation carries a mouse28 confirmation idempotency key', function (): void {
    config()->set('app.url', 'https://mouse28.test');
    $inquiry = ContactInquiry::factory()->make();
    $inquiry->id = 7;
    $createdAt = Date::parse('2026-10-02 12:00:00');
    $inquiry->created_at = $createdAt;

    $headers = new ContactMessageConfirmation($inquiry)->headers()
        ->text;

    expect($headers['Resend-Idempotency-Key'])->toBe('mouse28-contact-'.hash('sha256', "https://mouse28.test|7|{$createdAt->toISOString()}").'-confirmation');
});

test('contact confirmation html matches its snapshot', function (): void {
    URL::forceRootUrl('https://mouse28.test');

    $html = new ContactMessageConfirmation(ContactInquiry::factory()->make())->render();

    // Indentation and blank lines are not compared, so moving markup into a layout keeps the snapshot.
    $lines = array_filter(array_map(trim(...), explode("\n", $html)), fn (string $line): bool => $line !== '');

    expect(implode("\n", $lines))->toMatchSnapshot();
});
