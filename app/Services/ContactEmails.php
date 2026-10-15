<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContactMails;
use LogicException;

/**
 * Mouse28's own contact emails for creator-kit's contact job. Each mail builds the Resend
 * idempotency key Mouse28 has always used, so a retried send is discarded.
 */
final class ContactEmails implements ContactMails
{
    public function notification(Model $inquiry): ContactMessageReceived
    {
        return new ContactMessageReceived($this->contactInquiry($inquiry));
    }

    public function confirmation(Model $inquiry): ContactMessageConfirmation
    {
        return new ContactMessageConfirmation($this->contactInquiry($inquiry));
    }

    private function contactInquiry(Model $inquiry): ContactInquiry
    {
        if (! $inquiry instanceof ContactInquiry) {
            throw new LogicException('Mouse28\'s contact emails need a ContactInquiry.');
        }

        return $inquiry;
    }
}
