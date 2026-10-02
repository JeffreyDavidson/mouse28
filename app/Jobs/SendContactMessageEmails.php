<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Compatibility class for contact-mail jobs queued or failed before contact
 * messages became contact inquiries. The data migration keeps each message's ID,
 * so the job forwards it to SendContactInquiryEmails on the default queue.
 *
 * @deprecated Remove once the contact-mail queue and failed_jobs hold no instances (docs/operations.md).
 */
class SendContactMessageEmails implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $contactMessageId) {}

    public function handle(): void
    {
        dispatch(new SendContactInquiryEmails($this->contactMessageId));
    }
}
