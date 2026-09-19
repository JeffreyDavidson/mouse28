<?php

namespace App\Jobs;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\FailOnTimeout;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

#[Connection('database')]
#[Queue('contact-mail')]
#[Tries(3)]
#[Timeout(60)]
#[FailOnTimeout]
#[Backoff([60, 300, 900])]
class SendContactMessageEmails implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(public int $contactMessageId) {}

    /** @return list<WithoutOverlapping> */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping("contact-emails:{$this->contactMessageId}")
                ->withPrefix('')
                ->shared()
                ->releaseAfter(60)
                ->expireAfter(120),
        ];
    }

    public function handle(Mailer $mailer): void
    {
        $message = ContactMessage::query()->find($this->contactMessageId);
        if ($message === null) {
            return;
        }

        // Resend retains idempotency keys for 24 hours; delayed jobs need manual review.
        if ($message->created_at === null || $message->created_at->lt(Date::now()->subHours(23))) {
            $this->fail(new RuntimeException('Contact delivery requires manual review after 23 hours.'));

            return;
        }

        $message->email_attempted_at = Date::now();
        $message->save();

        if ($message->notification_sent_at === null) {
            $this->sendNotification($message, $mailer);
        }

        if ($message->confirmation_sent_at === null) {
            $this->sendConfirmation($message, $mailer);
        }
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

    private function sendNotification(ContactMessage $contactMessage, Mailer $mailer): void
    {
        try {
            $recipients = array_filter(array_map(trim(...), explode(',', Config::string('mail.admin_address'))));
            $sent = $mailer
                ->to($recipients)
                ->send(new ContactFormSubmitted($contactMessage));

            if ($sent === null) {
                return;
            }

            $contactMessage->notification_sent_at = Date::now();
            $contactMessage->save();
        } catch (Throwable $exception) {
            Log::error('Failed to send contact notification.', [
                'contact_message_id' => $contactMessage->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }

    private function sendConfirmation(ContactMessage $contactMessage, Mailer $mailer): void
    {
        try {
            $sent = $mailer
                ->to($contactMessage->email)
                ->send(new ContactFormConfirmation($contactMessage));

            if ($sent === null) {
                return;
            }

            $contactMessage->confirmation_sent_at = Date::now();
            $contactMessage->save();
        } catch (Throwable $exception) {
            Log::error('Failed to send contact confirmation.', [
                'contact_message_id' => $contactMessage->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }
}
