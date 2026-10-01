<?php

use App\Actions\SendNewsletterIssueTestEmail;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;

covers(SendNewsletterIssueTestEmail::class);

pest()->use(RefreshDatabase::class);

test('a test email goes to every configured admin address and records nothing', function (): void {
    Mail::fake();
    config()->set('mail.admin_address', 'jeffrey@example.test, cassie@example.test, ');
    $issue = NewsletterIssue::factory()->draft()->create();

    $recipients = app(SendNewsletterIssueTestEmail::class)->handle($issue);

    expect($recipients)->toBe(['jeffrey@example.test', 'cassie@example.test']);
    Mail::assertSent(NewsletterIssueMail::class, fn (NewsletterIssueMail $mail): bool => $mail->hasTo('jeffrey@example.test')
        && $mail->hasTo('cassie@example.test')
        && $mail->unsubscribeUrl === null);
    expect($issue->refresh()->wasSent())->toBeFalse();
    assertDatabaseCount(NewsletterDelivery::class, 0);
});
