<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JeffreyDavidson\CreatorKit\Jobs\SendContactInquiryEmails as PackageSendContactInquiryEmails;

/**
 * Compatibility class for contact-email jobs queued or failed before creator-kit took over
 * sending: it forwards the inquiry ID to the package job.
 *
 * @deprecated Remove once the queue and failed_jobs hold no instances (docs/operations.md).
 */
final class SendContactInquiryEmails implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(public int $contactInquiryId) {}

    public function handle(): void
    {
        dispatch(new PackageSendContactInquiryEmails($this->contactInquiryId));
    }
}
