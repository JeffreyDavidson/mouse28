<?php

namespace App\Http\Controllers;

use App\Support\Feeds\RssChannelRenderer;
use App\ViewModels\NewsletterRssFeedViewModel;
use Illuminate\Http\Response;

class NewsletterRssController
{
    public function __invoke(NewsletterRssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
