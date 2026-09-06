<?php

namespace App\Http\Controllers;

use App\Support\SitemapDocument;
use Illuminate\Http\Response;

class SitemapController
{
    public function index(SitemapDocument $sitemap): Response
    {
        return response($sitemap->content(), 200, ['Content-Type' => 'application/xml']);
    }
}
