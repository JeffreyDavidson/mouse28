<?php

use App\Jobs\SendContactInquiryEmails;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\AssertableJsonString;

covers(SendContactInquiryEmails::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('mail.admin_address', 'admin@example.com');
    config()->set('mail.default', 'array');
    Event::fake([MessageSent::class]);
});

function sendContactInquiryEmails(ContactInquiry $inquiry): void
{
    new SendContactInquiryEmails($inquiry->id)->handle(app(Mailer::class));
}

test('contact emails record their successful sends and are not resent', function (): void {
    $inquiry = ContactInquiry::factory()->create();

    sendContactInquiryEmails($inquiry);
    sendContactInquiryEmails($inquiry);
    $inquiry->refresh();

    expect($inquiry->email_attempted_at)->not->toBeNull()
        ->and($inquiry->notification_sent_at)->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('contact emails notify the administrators and confirm to the sender', function (): void {
    $inquiry = ContactInquiry::factory()->create(['name' => 'Dale Cooper', 'email' => 'dale@example.com']);

    sendContactInquiryEmails($inquiry);

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'admin@example.com'
        && $event->message->getReplyTo()[0]->getAddress() === 'dale@example.com');
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'dale@example.com');
});

test('contact confirmation goes to the bare sender address while the notification reply-to keeps their name', function (): void {
    $inquiry = ContactInquiry::factory()->create(['name' => 'Dale Cooper', 'email' => 'dale@example.com']);

    sendContactInquiryEmails($inquiry);

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'admin@example.com'
        && $event->message->getReplyTo()[0]->getName() === 'Dale Cooper');
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->getTo()[0]->getAddress() === 'dale@example.com'
        && $event->message->getTo()[0]->getName() === '');
});

test('retry sends only the contact email that previously failed', function (): void {
    $events = Event::fake([MessageSent::class]);
    Event::listen(MessageSending::class, function (MessageSending $event): void {
        if (str_starts_with($event->message->getSubject() ?? '', 'We got your message!')) {
            throw new RuntimeException('Simulated mail outage');
        }
    });
    $inquiry = ContactInquiry::factory()->create();

    expect(fn () => sendContactInquiryEmails($inquiry))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
    $inquiry->refresh();

    expect($inquiry->notification_sent_at)->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)->toBeNull();
    $notificationSentAt = $inquiry->notification_sent_at;
    $events->dispatcher->forget(MessageSending::class);

    sendContactInquiryEmails($inquiry);
    $inquiry->refresh();

    expect($inquiry->notification_sent_at)->toEqual($notificationSentAt)
        ->and($inquiry->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => str_starts_with($event->message->getSubject() ?? '', 'We got your message!'));
});

test('concurrent contact email sends are released while the inquiry is locked', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    $lock = Cache::lock("contact-emails:{$inquiry->id}", 120);
    $lock->get();
    $job = new SendContactInquiryEmails($inquiry->id);
    $job->withFakeQueueInteractions();

    try {
        $job->middleware()[0]->handle($job, function (SendContactInquiryEmails $job): void {
            $job->handle(app(Mailer::class));
        });

        $job->assertReleased(60);
        expect($inquiry->refresh()->email_attempted_at)->toBeNull();
        Event::assertNotDispatched(MessageSent::class);
    } finally {
        $lock->release();
    }
});

test('cancelled contact emails are not recorded as sent and remain retryable', function (): void {
    $events = Event::fake([MessageSent::class]);
    Event::listen(MessageSending::class, fn (): bool => false);
    $inquiry = ContactInquiry::factory()->create();

    expect(fn () => sendContactInquiryEmails($inquiry))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
    $inquiry->refresh();

    expect($inquiry->email_attempted_at)->not->toBeNull()
        ->and($inquiry->notification_sent_at)->toBeNull()
        ->and($inquiry->confirmation_sent_at)->toBeNull();
    Event::assertNotDispatched(MessageSent::class);

    $events->dispatcher->forget(MessageSending::class);

    sendContactInquiryEmails($inquiry);
    $inquiry->refresh();

    expect($inquiry->notification_sent_at)->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('contact emails support multiple configured administrator addresses', function (): void {
    config()->set('mail.admin_address', 'first@example.com, second@example.com, ');
    $inquiry = ContactInquiry::factory()->create();

    sendContactInquiryEmails($inquiry);
    $inquiry->refresh();

    expect($inquiry->notification_sent_at)->not->toBeNull()
        ->and($inquiry->confirmation_sent_at)->not->toBeNull();
    Event::assertDispatchedTimes(MessageSent::class, 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getTo()) === 2);
    Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => count($event->message->getReplyTo()) === 2);
});

test('contact emails ignore an inquiry that no longer exists', function (): void {
    new SendContactInquiryEmails(999)->handle(app(Mailer::class));

    Event::assertNotDispatched(MessageSent::class);
});

test('delivery middleware holds and releases the shared lock without resending successful mail', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    $job = new SendContactInquiryEmails($inquiry->id);

    $middleware = $job->middleware()[0];
    $send = function (SendContactInquiryEmails $job): void {
        expect(Cache::lock("contact-emails:{$job->contactInquiryId}", 120)->get())->toBeFalse();
        $job->handle(app(Mailer::class));
    };

    $middleware->handle($job, $send);
    $middleware->handle($job, $send);

    expect($middleware->expiresAfter)->toBe(120)
        ->and(Cache::lock("contact-emails:{$inquiry->id}", 120)->get(fn (): bool => true))->toBeTrue();
    Event::assertDispatchedTimes(MessageSent::class, 2);
});

test('contact jobs dispatched before commit are discarded when their transaction rolls back', function (): void {
    DB::beginTransaction();

    dispatch(new SendContactInquiryEmails(42))->beforeCommit();
    DB::rollBack();

    expect(DB::table('jobs')->count())->toBe(0);
});

test('delivery middleware releases the lock after an incomplete delivery', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    Event::listen(MessageSending::class, fn (): bool => false);
    $job = new SendContactInquiryEmails($inquiry->id);

    expect(fn () => $job->middleware()[0]->handle($job, function (SendContactInquiryEmails $job): void {
        $job->handle(app(Mailer::class));
    }))->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.')
        ->and(Cache::lock("contact-emails:{$inquiry->id}", 120)->get(fn (): bool => true))->toBeTrue();
});

test('queue attributes route contact delivery to the default database queue with worker settings', function (): void {
    Bus::dispatch(new SendContactInquiryEmails(42));
    $job = DB::table('jobs')->sole();

    if (! is_string($job->payload)) {
        throw new UnexpectedValueException('The contact job was not stored on its database queue.');
    }

    expect($job->queue)->toBe('default');
    new AssertableJsonString($job->payload)
        ->assertPath('maxTries', 3)
        ->assertPath('timeout', 60)
        ->assertPath('failOnTimeout', true)
        ->assertPath('backoff', '60,300,900');
});

test('queued contact jobs are encrypted and carry only the inquiry ID', function (): void {
    $inquiry = ContactInquiry::factory()->create(['name' => 'Private Sender', 'email' => 'private@example.test']);

    Bus::dispatch(new SendContactInquiryEmails($inquiry->id));
    $payload = DB::table('jobs')->value('payload');
    $command = data_get(json_decode(is_string($payload) ? $payload : '', true, flags: JSON_THROW_ON_ERROR), 'data.command');

    if (! is_string($command)) {
        throw new UnexpectedValueException('Expected an encrypted queued command.');
    }

    expect($command)->not->toContain(SendContactInquiryEmails::class)
        ->and(Crypt::decrypt($command))->toContain("i:{$inquiry->id};")
        ->not->toContain('Private Sender', 'private@example.test');
});

test('incomplete delivery fails the attempt so the worker retries it', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    Event::listen(MessageSending::class, fn (): bool => false);

    expect(fn () => sendContactInquiryEmails($inquiry))
        ->toThrow(RuntimeException::class, 'Contact email delivery is incomplete.');
});

test('old queued inquiries require manual review rather than automatic resending', function (): void {
    $inquiry = ContactInquiry::factory()->create(['created_at' => now()->subDay()]);
    $job = new SendContactInquiryEmails($inquiry->id)->withFakeQueueInteractions();

    $job->handle(app(Mailer::class));

    $job->assertFailedWith(new RuntimeException('Contact delivery requires manual review after 23 hours.'));
    expect($inquiry->refresh()->email_attempted_at)->toBeNull();
    Event::assertNotDispatched(MessageSent::class);
});

test('provider idempotency keys are stable and separate each recipient purpose', function (): void {
    $inquiry = ContactInquiry::factory()->create();
    $notification = new ContactMessageReceived($inquiry)->headers()->text;
    $confirmation = new ContactMessageConfirmation($inquiry)->headers()->text;

    expect($notification)->toBe(new ContactMessageReceived($inquiry->refresh())->headers()->text)
        ->and($notification)->not->toBe($confirmation);
});
