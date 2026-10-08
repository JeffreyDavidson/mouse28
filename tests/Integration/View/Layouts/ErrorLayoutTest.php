<?php

use App\Data\PageMeta;
use Illuminate\Support\Facades\Blade;
use RalphJSmit\Laravel\SEO\Support\SEOData;

test('the error layout renders the seo tags of the page meta it is given without a canonical link', function (): void {
    $pageMeta = new PageMeta(new SEOData(
        title: 'Gone | Mouse28',
        description: 'This page is gone.',
        enableTitleSuffix: false,
        robots: 'noindex, nofollow',
        canonical_url: 'http://localhost/gone',
    ));

    $html = Blade::render('<x-layouts.error :page-meta="$pageMeta" title="Ignored">Body</x-layouts.error>', ['pageMeta' => $pageMeta]);

    expect($html)
        ->toContain('<title>Gone | Mouse28</title>')
        ->toContain('<meta name="description" content="This page is gone.">')
        ->toContain('<meta name="robots" content="noindex, nofollow">')
        ->not->toContain('rel="canonical"')
        ->not->toContain('Ignored');
});
