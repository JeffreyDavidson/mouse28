<?php

use App\Actions\SendContactEmails;
use App\Jobs\DeliverContactEmails;
use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('mail.admin_address', 'admin@example.com');
    config()->set('mail.default', 'array');
    Event::fake([MessageSent::class]);
});

test('queued delivery uses a dedicated durable queue and does not resend successful mail', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    $job = new DeliverContactEmails($message->id);

    $job->handle(app(SendContactEmails::class));
    $job->handle(app(SendContactEmails::class));

    expect($job->connection)->toBe('database')
        ->and($job->queue)->toBe('contact-mail')
        ->and($job->afterCommit)->toBeTrue();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('incomplete delivery fails the attempt so the worker retries it', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    Event::listen(MessageSending::class, fn (): bool => false);

    expect(fn () => new DeliverContactEmails($message->id)->handle(app(SendContactEmails::class)))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
});

test('old queued messages require manual review rather than automatic resending', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    $message->created_at = now()->subDay();
    $message->save();
    $job = new DeliverContactEmails($message->id)->withFakeQueueInteractions();

    $job->handle(app(SendContactEmails::class));

    $job->assertFailedWith(new RuntimeException('Contact delivery requires manual review after 23 hours.'));
    Event::assertNotDispatched(MessageSent::class);
});

test('provider idempotency keys are stable and separate each recipient purpose', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    $notification = new ContactFormSubmitted($message)->headers()->text;
    $confirmation = new ContactFormConfirmation($message)->headers()->text;

    expect($notification)->toBe(new ContactFormSubmitted($message->refresh())->headers()->text)
        ->and($notification)->not->toBe($confirmation);
});
