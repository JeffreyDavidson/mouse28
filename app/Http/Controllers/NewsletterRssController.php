<?php

namespace App\Http\Controllers;

use App\ViewModels\NewsletterRssFeedViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\RssChannelRenderer;

class NewsletterRssController
{
    public function __invoke(NewsletterRssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
