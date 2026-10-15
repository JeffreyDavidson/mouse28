<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\NewsletterConfirmationMail;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\NewsletterMails;
use JeffreyDavidson\CreatorKit\Support\Mail\IdempotencyFingerprint;
use LogicException;

/**
 * Mouse28's own newsletter emails for creator-kit's newsletter actions and delivery job. A
 * subscriber's copy carries a Resend idempotency key built from its delivery, so a retried
 * send is discarded; the key is the one Mouse28 has always used.
 */
final class NewsletterEmails implements NewsletterMails
{
    public function confirmation(string $confirmationUrl): NewsletterConfirmationMail
    {
        return new NewsletterConfirmationMail($confirmationUrl);
    }

    public function issue(Model $issue, ?string $unsubscribeUrl = null, ?Model $delivery = null): NewsletterIssueMail
    {
        if (! $issue instanceof NewsletterIssue || ($delivery instanceof Model && ! $delivery instanceof NewsletterDelivery)) {
            throw new LogicException('Mouse28\'s newsletter email needs a NewsletterIssue and, for a subscriber\'s copy, its NewsletterDelivery.');
        }

        $idempotencyKey = $delivery instanceof NewsletterDelivery
            ? 'mouse28-newsletter-'.IdempotencyFingerprint::for($delivery->id, $delivery->created_at)
            : null;

        return new NewsletterIssueMail($issue, $unsubscribeUrl, $idempotencyKey);
    }
}
