<?php

use App\Mail\NewsletterConfirmationMail;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

covers(NewsletterConfirmationMail::class);

test('the confirmation mail keeps the signed link intact in plain text', function (): void {
    $url = 'https://mouse28.test/newsletter/confirm/1/token?expires=123&signature=abc';

    $mail = new NewsletterConfirmationMail($url);

    $mail->assertSeeInText($url);
    $mail->assertSeeInText('This link expires in 24 hours.');
    $mail->assertSeeInText('Mouse28');
    expect($mail->envelope()->subject)->toBe('Confirm your Mouse28 newsletter sign-up');
});

test('the queued confirmation mail is encrypted', function (): void {
    expect(class_implements(NewsletterConfirmationMail::class))->toContain(ShouldBeEncrypted::class);
});
