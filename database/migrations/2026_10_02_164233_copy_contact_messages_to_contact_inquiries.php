<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy every contact message into contact_inquiries with the same ID and
     * timestamps, so delivery stamps, provider idempotency keys and queued
     * legacy jobs keep pointing at the same record. Personal fields are
     * encrypted as the model's `encrypted` cast stores them. The source table
     * is kept until the release is verified; dropping it is a later step.
     */
    public function up(): void
    {
        DB::table('contact_messages')->orderBy('id')->chunk(100, function ($messages): void {
            DB::table('contact_inquiries')->insert($messages->map(function (object $message): array {
                $type = ContactType::tryFrom($message->subject);
                $body = $type === null && trim($message->subject) !== ''
                    ? "Subject: {$message->subject}\n\n{$message->message}"
                    : $message->message;

                return [
                    'id' => $message->id,
                    'name' => Crypt::encryptString($message->name),
                    'email' => Crypt::encryptString($message->email),
                    'type' => ($type ?? ContactType::Other)->value,
                    'message' => Crypt::encryptString($body),
                    'status' => $this->statusForLegacyReadFlag((bool) $message->is_read)->value,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    'email_attempted_at' => $message->email_attempted_at,
                    'notification_sent_at' => $message->notification_sent_at,
                    'confirmation_sent_at' => $message->confirmation_sent_at,
                ];
            })->all());
        });
    }

    /**
     * OWNER DECISION PENDING: how the former read flag maps to a status.
     * Unread stays `new` (today's unread badge); read becomes `in_progress`,
     * which keeps the distinction one-to-one without claiming the message was
     * resolved. Change only this mapping if the owner decides otherwise.
     */
    private function statusForLegacyReadFlag(bool $isRead): ContactInquiryStatus
    {
        return $isRead
            ? ContactInquiryStatus::InProgress
            : ContactInquiryStatus::New;
    }
};
