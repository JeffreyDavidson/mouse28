<?php

declare(strict_types=1);

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
            'Sitemap: '.route('sitemap'),
        ]);
    }
}
