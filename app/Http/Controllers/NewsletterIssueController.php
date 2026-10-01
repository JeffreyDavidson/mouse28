<?php

namespace App\Http\Controllers;

use App\Models\NewsletterIssue;
use App\ViewModels\NewsletterIndexViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterIssueController
{
    public function index(Request $request, NewsletterIndexViewModel $viewModel): View
    {
        return view('pages.newsletter.index', $viewModel->data($request));
    }

    public function show(NewsletterIssue $newsletterIssue): View
    {
        abort_unless($newsletterIssue->isPublished(), 404);

        return view('pages.newsletter.issue', ['issue' => $newsletterIssue]);
    }
}
