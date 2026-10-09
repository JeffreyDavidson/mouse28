<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use Database\Factories\ContactInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * A message sent through the public contact form. Contact details are encrypted
 * at rest. Inquiries are kept until an administrator deletes them, so this model
 * is intentionally not prunable (The Laravel Architect prunes; see .ai/rules/models.md).
 *
 * @method static Builder<static> new()
 *
 * @property ContactType $type
 * @property ContactInquiryStatus $status
 */
#[Fillable([
    'name',
    'email',
    'type',
    'message',
    'status',
    'email_attempted_at',
    'notification_sent_at',
    'confirmation_sent_at',
])]
class ContactInquiry extends Model
{
    /** @use HasFactory<ContactInquiryFactory> */
    use HasFactory;

    /**
     * Resend keeps idempotency keys for 24 hours, so emails are only retried
     * within 23 hours of submission; later resends could deliver duplicates.
     */
    public const int EMAIL_RETRY_WINDOW_HOURS = 23;

    /**
     * Inquiries nobody has opened yet.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function new(Builder $query): void
    {
        $query->where('status', ContactInquiryStatus::New);
    }

    public function isNew(): bool
    {
        return $this->status === ContactInquiryStatus::New;
    }

    /**
     * Whether the provider still honours the idempotency keys for this inquiry's emails.
     */
    public function canRetryEmails(): bool
    {
        return $this->created_at !== null
            && $this->created_at->gte(Date::now()->subHours(self::EMAIL_RETRY_WINDOW_HOURS));
    }

    /**
     * Build a reply draft link, percent-encoding values so spaces stay spaces and
     * address characters such as `?` or `&` cannot add mailto header fields.
     */
    public function replyMailtoUrl(): string
    {
        $address = str_replace('%40', '@', rawurlencode($this->email));
        $subject = rawurlencode("Re: {$this->type->getLabel()}");

        return "mailto:{$address}?subject={$subject}";
    }

    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'email' => 'encrypted',
            'message' => 'encrypted',
            'type' => ContactType::class,
            'status' => ContactInquiryStatus::class,
            'email_attempted_at' => 'datetime',
            'notification_sent_at' => 'datetime',
            'confirmation_sent_at' => 'datetime',
        ];
    }
}
