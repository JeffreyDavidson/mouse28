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
        && $mail->idempotencyKey === 'mouse28-newsletter-'.hash('sha256', config()->string('app.url')."|{$delivery->id}"));
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
