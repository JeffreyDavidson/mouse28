<?php

use App\Jobs\DeliverNewsletterIssue;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;

covers(DeliverNewsletterIssue::class);

pest()->use(RefreshDatabase::class);

function runDelivery(NewsletterDelivery $delivery): void
{
    app()->call([new DeliverNewsletterIssue($delivery), 'handle']);
}

test('a delivery emails the issue with a personal unsubscribe link and is marked sent', function (): void {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()->create();

    runDelivery($delivery);

    $recipient = Subscriber::query()->findOrFail($delivery->subscriber_id)->email;
    Mail::assertSent(NewsletterIssueMail::class, fn (NewsletterIssueMail $mail): bool => $mail->hasTo($recipient)
        && $mail->issue->id === $delivery->newsletter_issue_id
        && str_contains((string) $mail->unsubscribeUrl, 'signature=')
        && $mail->idempotencyKey === 'mouse28-newsletter-'.hash('sha256', implode('|', [config()->string('app.url'), $delivery->id, $delivery->created_at?->toISOString()])));
    expect($delivery->refresh()->sent_at)->not->toBeNull();
});

test('a delivery that was already sent is never emailed again', function (): void {
    Mail::fake();

    runDelivery(NewsletterDelivery::factory()->sent()->create());

    Mail::assertNothingSent();
});

test('a delivery is dropped when the reader is no longer active', function (): void {
    Mail::fake();
    $delivery = NewsletterDelivery::factory()->create();
    $delivery->subscriber?->update(['unsubscribed_at' => now()]);

    runDelivery($delivery);

    Mail::assertNothingSent();
    $this->assertModelMissing($delivery);
});

test('a delivery stays unsent when the mail transport fails so it can be tried again', function (): void {
    $delivery = NewsletterDelivery::factory()->create();
    Mail::shouldReceive('to')->andThrow(new RuntimeException('mail transport unavailable'));

    expect(fn () => runDelivery($delivery))->toThrow(RuntimeException::class, 'mail transport unavailable')
        ->and($delivery->refresh()->sent_at)->toBeNull();
});

test('deliveries go out through the newsletter send rate limit', function (): void {
    $job = new DeliverNewsletterIssue(NewsletterDelivery::factory()->create());

    expect($job->middleware())->toEqual([new RateLimited('newsletter-delivery')]);
});

test('a delivery is abandoned before the queue hands it to another worker', function (): void {
    // Arrange
    $job = new DeliverNewsletterIssue(NewsletterDelivery::factory()->create());

    // Act
    $timeout = $job->timeout;

    // Assert
    expect($timeout)->toBe(60)
        ->and($timeout)->toBeLessThan(config()->integer('queue.connections.database.retry_after'));
});

test('a delivery key stays the same across attempts', function (): void {
    // Arrange
    Mail::fake();
    $delivery = NewsletterDelivery::factory()->create();

    // Act
    runDelivery($delivery);
    $delivery->update(['sent_at' => null]);
    runDelivery($delivery->refresh());

    // Assert
    $keys = Mail::sent(NewsletterIssueMail::class)->pluck('idempotencyKey');
    expect($keys)->toHaveCount(2)
        ->and($keys->unique())->toHaveCount(1);
});

test('a delivery key differs when a database reset reuses the same delivery id', function (): void {
    // Arrange
    Mail::fake();
    $first = NewsletterDelivery::factory()->create(['created_at' => '2026-10-01 12:00:00']);
    runDelivery($first);
    $reusedId = $first->id;
    $first->delete();
    $second = NewsletterDelivery::factory()->create(['id' => $reusedId, 'created_at' => '2026-10-06 12:00:00']);

    // Act
    runDelivery($second);

    // Assert
    $keys = Mail::sent(NewsletterIssueMail::class)->pluck('idempotencyKey');
    expect($keys)->toHaveCount(2)
        ->and($keys->unique())->toHaveCount(2);
});
