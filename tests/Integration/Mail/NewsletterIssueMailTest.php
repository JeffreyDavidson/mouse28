<?php

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(NewsletterIssueMail::class);

pest()->use(RefreshDatabase::class);

function issueForMail(string $content = 'Hello **readers**.'): NewsletterIssue
{
    return NewsletterIssue::factory()->create([
        'title' => 'Issue One & Beyond',
        'slug' => 'issue-one',
        'content' => $content,
    ]);
}

test('an issue email carries the title, the Markdown body and both links', function (): void {
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc&x=1';

    $mail = new NewsletterIssueMail(issueForMail(), $unsubscribeUrl);

    $mail->assertHasSubject('Issue One & Beyond')
        ->assertSeeInHtml('<strong>readers</strong>', false)
        ->assertSeeInHtml(route('newsletter.issue', 'issue-one'))
        ->assertSeeInHtml($unsubscribeUrl)
        ->assertSeeInText('Hello **readers**.')
        ->assertSeeInText(route('newsletter.issue', 'issue-one'))
        ->assertSeeInText($unsubscribeUrl);
});

test('raw HTML and unsafe links in the content never reach the email', function (): void {
    $mail = new NewsletterIssueMail(issueForMail("<script>alert('x')</script>\n\n[Click](javascript:alert(1))"), 'https://example.test/unsubscribe');

    $mail->assertDontSeeInHtml('<script>', false)
        ->assertDontSeeInHtml('javascript:', false);
});

test('a reader email offers one-click unsubscribe and a duplicate-proof key', function (): void {
    $unsubscribeUrl = 'https://example.test/newsletter/unsubscribe/1?signature=abc';

    $headers = new NewsletterIssueMail(issueForMail(), $unsubscribeUrl, 'mouse28-newsletter-key')->headers();

    expect($headers->text)->toBe([
        'List-Unsubscribe' => "<{$unsubscribeUrl}>",
        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        'Resend-Idempotency-Key' => 'mouse28-newsletter-key',
    ]);
});

test('a test email is marked and carries no unsubscribe headers', function (): void {
    $mail = new NewsletterIssueMail(issueForMail());

    $mail->assertSeeInHtml('This is a test email')
        ->assertSeeInText('This is a test email');
    expect($mail->headers()->text)->toBeEmpty();
});
