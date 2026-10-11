<?php

namespace App\Http\Controllers;

use App\ViewModels\RssFeedViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\RssChannelRenderer;

class BlogRssController
{
    public function __invoke(RssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'application/rss+xml']);
    }
}
