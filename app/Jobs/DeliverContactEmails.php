<?php

namespace App\Jobs;

use App\Actions\SendContactEmails;
use App\Models\ContactMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DeliverContactEmails implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public int $contactMessageId)
    {
        $this->onConnection('database');
        $this->onQueue('contact-mail');
        $this->afterCommit();
    }

    public function handle(SendContactEmails $send): void
    {
        $message = ContactMessage::query()->find($this->contactMessageId);
        if ($message === null) {
            return;
        }

        // Resend retains idempotency keys for 24 hours; delayed jobs need manual review.
        if ($message->created_at === null || $message->created_at->lt(now()->subHours(23))) {
            $this->fail(new RuntimeException('Contact delivery requires manual review after 23 hours.'));

            return;
        }

        $send($message);
        $message->refresh();
        if ($message->notification_sent_at === null || $message->confirmation_sent_at === null) {
            throw new RuntimeException('Contact email delivery is incomplete.');
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Contact email delivery requires manual review.', [
            'contact_message_id' => $this->contactMessageId,
        ]);
    }
}
