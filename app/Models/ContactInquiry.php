<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactType;
use Database\Factories\ContactInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContactInquiry as ContactInquiryContract;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;
use JeffreyDavidson\CreatorKit\Models\Concerns\IsContactInquiry;

/**
 * A message sent through the public contact form. creator-kit's IsContactInquiry encrypts the
 * contact details at rest and holds the status, email-retry and retention rules. Inquiries are
 * kept until an administrator deletes them: `creator-kit.contact.retention_days` is null, so
 * the trait's prunable() matches nothing (The Laravel Architect prunes; see .ai/rules/models.md).
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
class ContactInquiry extends Model implements ContactInquiryContract
{
    /** @use HasFactory<ContactInquiryFactory> */
    use HasFactory, IsContactInquiry;

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
            'type' => ContactType::class,
        ];
    }
}
