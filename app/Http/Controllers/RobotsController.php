<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /preview/',
            'Disallow: /search',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}
