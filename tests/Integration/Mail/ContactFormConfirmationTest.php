<?php

use App\Mail\ContactFormConfirmation;
use App\Models\ContactMessage;

test('contact confirmation uses the configured contact address for replies', function (): void {
    config()->set('mail.admin_address', 'hello@mouse28.test');
    $contactMessage = new ContactMessage([
        'email' => 'dale@example.com',
        'subject' => 'accessibility',
    ]);

    $envelope = (new ContactFormConfirmation($contactMessage))->envelope();

    expect($envelope->subject)->toBe('We got your message! — Mouse28')
        ->and($envelope->replyTo)->toHaveCount(1)
        ->and($envelope->replyTo[0]->address)->toBe('hello@mouse28.test');
});

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
