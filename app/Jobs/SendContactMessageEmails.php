<?php

namespace App\Jobs;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendContactMessageEmails implements ShouldQueue
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

        Cache::lock("contact-emails:{$message->id}", 120)->get(function () use ($message, $mailer): void {
            $message->refresh();
            $message->email_attempted_at = Date::now();
            $message->save();

            if ($message->notification_sent_at === null) {
                $this->sendNotification($message, $mailer);
            }

            if ($message->confirmation_sent_at === null) {
                $this->sendConfirmation($message, $mailer);
            }
        });
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
