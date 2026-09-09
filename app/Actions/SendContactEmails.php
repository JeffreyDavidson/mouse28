<?php

namespace App\Actions;

use App\Mail\ContactFormConfirmation;
use App\Mail\ContactFormSubmitted;
use App\Models\ContactMessage;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendContactEmails
{
    public function __construct(private readonly Mailer $mailer) {}

    public function __invoke(ContactMessage $contactMessage): void
    {
        try {
            $recipients = array_filter(array_map(trim(...), explode(',', (string) config('mail.admin_address'))));
            $this->mailer
                ->to($recipients)
                ->send(new ContactFormSubmitted($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Failed to send contact notification.', [
                'contact_message_id' => $contactMessage->getKey(),
                'exception' => $exception::class,
            ]);
        }

        try {
            $this->mailer
                ->to($contactMessage->email)
                ->send(new ContactFormConfirmation($contactMessage));
        } catch (Throwable $exception) {
            Log::error('Failed to send contact confirmation.', [
                'contact_message_id' => $contactMessage->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }
}
