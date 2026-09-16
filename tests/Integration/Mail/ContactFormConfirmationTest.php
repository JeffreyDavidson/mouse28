<?php

use App\Mail\ContactFormConfirmation;
use App\Models\ContactMessage;
use Illuminate\Mail\Mailables\Address;

test('contact confirmation uses configured contact addresses for replies', function (string $configured, array $addresses): void {
    config()->set('mail.admin_address', $configured);
    $contactMessage = new ContactMessage([
        'email' => 'dale@example.com',
        'subject' => 'accessibility',
    ]);

    $envelope = new ContactFormConfirmation($contactMessage)->envelope();

    expect($envelope->subject)->toBe('We got your message! — Mouse28')
        ->and($envelope->replyTo)->toEqual($addresses);
})->with([
    'one recipient' => ['hello@mouse28.test', [new Address('hello@mouse28.test')]],
    'multiple recipients with whitespace' => ['hello@mouse28.test, second@mouse28.test, ', [new Address('hello@mouse28.test'), new Address('second@mouse28.test')]],
]);

test('contact confirmation renders the contact details safely', function (): void {
    $contactMessage = new ContactMessage([
        'name' => 'Dale <Cooper>',
        'email' => 'dale@example.com',
        'subject' => 'accessibility',
        'message' => '<script>alert("unsafe")</script> Need accessibility help.',
    ]);
    $mailable = new ContactFormConfirmation($contactMessage);

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
