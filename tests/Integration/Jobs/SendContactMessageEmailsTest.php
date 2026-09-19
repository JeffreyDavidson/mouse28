<?php

use App\Jobs\SendContactMessageEmails;
use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\AssertableJsonString;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('mail.admin_address', 'admin@example.com');
    config()->set('mail.default', 'array');
    Event::fake([MessageSent::class]);
});

test('contact emails record their successful sends and are not resent', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);
    $job = new SendContactMessageEmails($message->id);

    $job->handle(app(Mailer::class));
    $job->handle(app(Mailer::class));
    $message->refresh();

    expect($message->email_attempted_at)->not->toBeNull()
        ->and($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('retry sends only the contact email that previously failed', function (): void {
    $events = Event::fake([MessageSent::class]);
    Event::listen(MessageSending::class, function (MessageSending $event): void {
        if (str_starts_with($event->message->getSubject() ?? '', 'We got your message!')) {
            throw new RuntimeException('Simulated mail outage');
        }
    });
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);

    expect(fn () => new SendContactMessageEmails($message->id)->handle(app(Mailer::class)))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
    $message->refresh();

    expect($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->toBeNull();
    $notificationSentAt = $message->notification_sent_at;
    $events->dispatcher->forget(MessageSending::class);

    new SendContactMessageEmails($message->id)->handle(app(Mailer::class));
    $message->refresh();

    expect($message->notification_sent_at)->toEqual($notificationSentAt)
        ->and($message->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => str_starts_with($event->message->getSubject() ?? '', 'We got your message!'));
});

test('concurrent contact email sends do not send while the message is locked', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);
    $lock = Cache::lock("contact-emails:{$message->id}", 120);
    $lock->get();

    try {
        expect(fn () => new SendContactMessageEmails($message->id)->handle(app(Mailer::class)))
            ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');

        Event::assertNotDispatched(MessageSent::class);
    } finally {
        $lock->release();
    }
});

test('cancelled contact emails are not recorded as sent and remain retryable', function (): void {
    $events = Event::fake([MessageSent::class]);
    Event::listen(MessageSending::class, fn (): bool => false);
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);

    expect(fn () => new SendContactMessageEmails($message->id)->handle(app(Mailer::class)))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
    $message->refresh();

    expect($message->email_attempted_at)->not->toBeNull()
        ->and($message->notification_sent_at)->toBeNull()
        ->and($message->confirmation_sent_at)->toBeNull();
    Event::assertNotDispatched(MessageSent::class);

    $events->dispatcher->forget(MessageSending::class);

    new SendContactMessageEmails($message->id)->handle(app(Mailer::class));
    $message->refresh();

    expect($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('contact emails support multiple configured administrator addresses', function (): void {
    config()->set('mail.admin_address', 'first@example.com, second@example.com, ');
    $message = ContactMessage::query()->create([
        'name' => 'Dale Cooper', 'email' => 'dale@example.com',
        'subject' => 'general', 'message' => 'A park question.',
    ]);

    new SendContactMessageEmails($message->id)->handle(app(Mailer::class));
    $message->refresh();

    expect($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getTo()) === 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getReplyTo()) === 2);
});

test('queued delivery uses a dedicated durable queue and does not resend successful mail', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    $job = new SendContactMessageEmails($message->id);

    $job->handle(app(Mailer::class));
    $job->handle(app(Mailer::class));

    expect($job->afterCommit)->toBeTrue();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('queue attributes configure contact delivery routing and worker settings', function (): void {
    $job = new SendContactMessageEmails(42);
    expect($job->afterCommit)->toBeTrue();
    $job->beforeCommit();

    Bus::dispatch($job);
    $payload = DB::table('jobs')->where('queue', 'contact-mail')->value('payload');

    if (! is_string($payload)) {
        throw new UnexpectedValueException('The contact job was not stored on its database queue.');
    }

    new AssertableJsonString($payload)
        ->assertPath('maxTries', 3)
        ->assertPath('timeout', 60)
        ->assertPath('failOnTimeout', true)
        ->assertPath('backoff', '60,300,900');
});

test('incomplete delivery fails the attempt so the worker retries it', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    Event::listen(MessageSending::class, fn (): bool => false);

    expect(fn () => new SendContactMessageEmails($message->id)->handle(app(Mailer::class)))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
});

test('old queued messages require manual review rather than automatic resending', function (): void {
    $message = ContactMessage::query()->create([
        'name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'general', 'message' => 'A question.',
    ]);
    $message->created_at = now()->subDay();
    $message->save();
    $job = new SendContactMessageEmails($message->id)->withFakeQueueInteractions();

    $job->handle(app(Mailer::class));

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
