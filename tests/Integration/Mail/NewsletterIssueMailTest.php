<?php

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

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

test('a single line break in the content stays a soft break in the email html', function (): void {
    $mail = new NewsletterIssueMail(issueForMail("First line\nSecond line"), 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml("First line\nSecond line", false)
        ->assertDontSeeInHtml('First line<br', false);
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
    expect($mail->headers()
        ->text)->toBeEmpty();
});

test('relative links and images become absolute in the html and text parts', function (): void {
    config()->set('app.url', 'https://example.test');
    $issue = issueForMail("[Post](/blog/x) and [Rel](blog/y)\n\n![Pic](/storage/pic.png)\n\n[Paged](/blog?page=2&a=1)");

    $mail = new NewsletterIssueMail($issue, 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('href="https://example.test/blog/x"', false)
        ->assertSeeInHtml('href="https://example.test/blog/y"', false)
        ->assertSeeInHtml('src="https://example.test/storage/pic.png"', false)
        ->assertSeeInHtml('href="https://example.test/blog?page=2&amp;a=1"', false)
        ->assertDontSeeInHtml('href="/blog', false)
        ->assertSeeInText('[Post](https://example.test/blog/x)')
        ->assertSeeInText('![Pic](https://example.test/storage/pic.png)')
        ->assertDontSeeInText('](/');
});

test('absolute, mailto, tel, anchor and protocol-relative links are left alone', function (): void {
    config()->set('app.url', 'https://example.test');
    $markdown = '[A](https://other.test/a) [B](mailto:me@example.com) [C](tel:+15555550100) [D](#section) [E](//cdn.test/e)';

    $mail = new NewsletterIssueMail(issueForMail($markdown), 'https://example.test/unsubscribe');

    $mail->assertSeeInHtml('href="https://other.test/a"', false)
        ->assertSeeInHtml('href="mailto:me@example.com"', false)
        ->assertSeeInHtml('href="tel:+15555550100"', false)
        ->assertSeeInHtml('href="#section"', false)
        ->assertSeeInHtml('href="//cdn.test/e"', false)
        ->assertSeeInText($markdown);
});

test('an issue email html matches its snapshot for readers and for tests', function (?string $unsubscribeUrl): void {
    URL::forceRootUrl('https://mouse28.test');

    $html = new NewsletterIssueMail(issueForMail("## Park day\n\nHello **readers**.\n\n> A quote"), $unsubscribeUrl)->render();

    // Indentation and blank lines are not compared, so moving markup into a layout keeps the snapshot.
    $lines = array_filter(array_map(trim(...), explode("\n", $html)), fn (string $line): bool => $line !== '');

    expect(implode("\n", $lines))->toMatchSnapshot();
})->with([
    'reader' => ['https://mouse28.test/newsletter/unsubscribe/1?signature=abc'],
    'test email' => [null],
]);
