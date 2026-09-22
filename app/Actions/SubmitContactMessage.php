<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\SendContactMessageEmails;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

class SubmitContactMessage
{
    /** @param array{name: string, email: string, subject: string, message: string} $attributes */
    public function __invoke(array $attributes): void
    {
        DB::transaction(function () use ($attributes): void {
            $contactMessage = ContactMessage::query()->create($attributes);
            SendContactMessageEmails::dispatch($contactMessage->id);
        });
    }
}
