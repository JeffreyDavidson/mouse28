<?php

namespace App\Support;

class RobotsDocument
{
    public function content(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /preview/',
            'Disallow: /search',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ]);
    }
}
