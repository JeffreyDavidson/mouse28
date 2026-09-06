<?php

namespace App\Http\Controllers;

use App\Support\BlogRssFeed;
use Illuminate\Http\Response;

class RssController
{
    public function __invoke(BlogRssFeed $feed): Response
    {
        return response($feed->content(), 200, ['Content-Type' => 'application/rss+xml']);
    }
}
