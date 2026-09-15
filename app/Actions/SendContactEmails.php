<?php

namespace App\Actions;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendContactEmails
{
    public function __construct(private readonly Mailer $mailer) {}

    public function __invoke(ContactMessage $contactMessage): void
    {
        Cache::lock("contact-emails:{$contactMessage->id}", 120)->get(function () use ($contactMessage): void {
            $contactMessage->refresh();
            $contactMessage->email_attempted_at = Date::now();
            $contactMessage->save();

            if ($contactMessage->notification_sent_at === null) {
                $this->sendNotification($contactMessage);
            }

            if ($contactMessage->confirmation_sent_at === null) {
                $this->sendConfirmation($contactMessage);
            }
        });
    }

    private function sendNotification(ContactMessage $contactMessage): void
    {
        try {
            $recipients = array_filter(array_map(trim(...), explode(',', Config::string('mail.admin_address'))));
            $sent = $this->mailer
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

    private function sendConfirmation(ContactMessage $contactMessage): void
    {
        try {
            $sent = $this->mailer
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
