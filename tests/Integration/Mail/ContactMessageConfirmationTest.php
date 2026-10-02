<?php

use App\Enums\ContactType;
use App\Mail\ContactMessageConfirmation;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Date;

test('contact confirmation uses configured contact addresses for replies', function (string $configured, array $addresses): void {
    config()->set('mail.admin_address', $configured);
    $inquiry = ContactInquiry::factory()->make(['type' => ContactType::Accessibility]);

    $envelope = new ContactMessageConfirmation($inquiry)->envelope();

    expect($envelope->subject)->toBe('We got your message! — Mouse28')
        ->and($envelope->replyTo)->toEqual($addresses);
})->with([
    'one recipient' => ['hello@mouse28.test', [new Address('hello@mouse28.test')]],
    'multiple recipients with whitespace' => ['hello@mouse28.test, second@mouse28.test, ', [new Address('hello@mouse28.test'), new Address('second@mouse28.test')]],
]);

test('contact confirmation renders the contact details safely', function (): void {
    $inquiry = ContactInquiry::factory()->make([
        'name' => 'Dale <Cooper>',
        'email' => 'dale@example.com',
        'type' => ContactType::Accessibility,
        'message' => '<script>alert("unsafe")</script> Need accessibility help.',
    ]);
    $mailable = new ContactMessageConfirmation($inquiry);

    $content = $mailable->content();
    $html = $mailable->render();

    expect($content->view)->toBe('emails.contact-confirmation')
        ->and($html)->toContain('Hi Dale &lt;Cooper&gt;,')
        ->and($html)->toContain('Park Accessibility')
        ->and($html)->toContain('&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt; Need accessibility help.')
        ->and($html)->not->toContain('<script>alert("unsafe")</script>')
        ->and($html)->toContain(route('episodes.index'))
        ->and($html)->toContain(route('blog.index'));
});

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

    $headers = new ContactMessageConfirmation($inquiry)->headers()->text;

    expect($headers['Resend-Idempotency-Key'])->toBe('mouse28-contact-'.hash('sha256', "https://mouse28.test|7|{$createdAt->toISOString()}").'-confirmation');
});
