<?php

namespace App\Actions;

use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterIssue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

final class SendNewsletterIssueTestEmail
{
    /**
     * Email the issue to the site's admin addresses without recording a delivery.
     *
     * @return list<string> The addresses the test email was sent to.
     */
    public function handle(NewsletterIssue $issue): array
    {
        $recipients = array_values(array_filter(array_map(trim(...), explode(',', Config::string('mail.admin_address')))));

        Mail::to($recipients)->send(new NewsletterIssueMail($issue));

        return $recipients;
    }
}
