<?php

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Services\NewsletterEmails;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterMails;

covers(NewsletterEmails::class);

pest()->use(RefreshDatabase::class);

test('creator-kit gets Mouse28 newsletter emails', function (): void {
    expect(app(NewsletterMails::class)::class)->toBe(NewsletterEmails::class);
});

test('the confirmation email carries the confirmation link', function (): void {
    // Act
    $mail = new NewsletterEmails()->confirmation('https://example.test/confirm');

    // Assert
    expect($mail->confirmationUrl)->toBe('https://example.test/confirm');
});

test('a subscriber copy carries the unsubscribe link and the delivery idempotency key Mouse28 has always used', function (): void {
    // Arrange
    $delivery = NewsletterDelivery::factory()->create();
    $issue = $delivery->newsletterIssue;

    // Act
    $mail = new NewsletterEmails()->issue($issue ?? throw new RuntimeException('Missing issue.'), 'https://example.test/unsubscribe', $delivery);

    // Assert
    expect($mail->unsubscribeUrl)->toBe('https://example.test/unsubscribe')
        ->and($mail->idempotencyKey)
        ->toBe('mouse28-newsletter-'.hash('sha256', implode('|', [config()->string('app.url'), $delivery->id, $delivery->created_at?->toISOString()])));
});

test('a test send has no unsubscribe link and no idempotency key', function (): void {
    // Arrange
    $issue = NewsletterIssue::factory()->create();

    // Act
    $mail = new NewsletterEmails()->issue($issue);

    // Assert
    expect($mail->unsubscribeUrl)->toBeNull()
        ->and($mail->idempotencyKey)
        ->toBeNull();
});

test('another model is refused instead of being emailed', function (): void {
    // Arrange
    $post = Post::factory()->create();

    // Act
    $sendPost = fn (): NewsletterIssueMail => new NewsletterEmails()->issue($post);

    // Assert
    expect($sendPost)->toThrow(LogicException::class);
});
