<?php

namespace App\Actions;

use App\Models\ContactMessage;

class SubmitContactMessage
{
    public function __construct(private readonly SendContactEmails $sendContactEmails) {}

    /** @param array{name: string, email: string, subject: string, message: string} $attributes */
    public function __invoke(array $attributes): void
    {
        $contactMessage = ContactMessage::query()->create($attributes);

        ($this->sendContactEmails)($contactMessage);
    }
}
