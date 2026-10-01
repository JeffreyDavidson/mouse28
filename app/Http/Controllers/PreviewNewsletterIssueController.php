<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use Illuminate\View\View;

class PreviewNewsletterIssueController
{
    public function __invoke(NewsletterIssue $newsletterIssue): View
    {
        return view('pages.newsletter.issue', ['issue' => $newsletterIssue, 'isPreview' => true]);
    }
}
