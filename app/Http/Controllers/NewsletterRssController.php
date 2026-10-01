<?php

namespace App\Http\Controllers;

use App\Actions\GenerateNewsletterRssFeed;
use Illuminate\Http\Response;

class NewsletterRssController
{
    public function __invoke(GenerateNewsletterRssFeed $generateNewsletterRssFeed): Response
    {
        return response($generateNewsletterRssFeed->handle(), 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
