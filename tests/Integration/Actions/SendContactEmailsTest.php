<?php

use App\Actions\SendContactEmails;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

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
    $send = app(SendContactEmails::class);

    $send($message);
    $send($message);
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

    app(SendContactEmails::class)($message);
    $message->refresh();

    expect($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->toBeNull();
    $notificationSentAt = $message->notification_sent_at;
    $events->dispatcher->forget(MessageSending::class);

    app(SendContactEmails::class)($message);
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
        app(SendContactEmails::class)($message);

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

    app(SendContactEmails::class)($message);
    $message->refresh();

    expect($message->email_attempted_at)->not->toBeNull()
        ->and($message->notification_sent_at)->toBeNull()
        ->and($message->confirmation_sent_at)->toBeNull();
    Event::assertNotDispatched(MessageSent::class);

    $events->dispatcher->forget(MessageSending::class);

    app(SendContactEmails::class)($message);
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

    app(SendContactEmails::class)($message);
    $message->refresh();

    expect($message->notification_sent_at)->not->toBeNull()
        ->and($message->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getTo()) === 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getReplyTo()) === 2);
});
