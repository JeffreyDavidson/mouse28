<?php

use App\Data\PageMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ViewErrorBag;
use RalphJSmit\Laravel\SEO\Support\SEOData;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => view()->share('errors', new ViewErrorBag));

test('the site layout renders the seo tags of the page meta it is given', function (): void {
    $pageMeta = new PageMeta(new SEOData(
        title: 'A Page | Mouse28',
        description: 'A page description.',
        enableTitleSuffix: false,
        robots: 'noindex,follow',
        canonical_url: 'http://localhost/a-page',
        openGraphTitle: 'A Page',
    ));

    $html = Blade::render('<x-layouts.app :page-meta="$pageMeta" title="Ignored">Body</x-layouts.app>', ['pageMeta' => $pageMeta]);

    expect($html)
        ->toContain('<title>A Page | Mouse28</title>')
        ->toContain('<meta name="description" content="A page description.">')
        ->toContain('<meta name="robots" content="noindex,follow">')
        ->toContain('<link rel="canonical" href="http://localhost/a-page">')
        ->toContain('<meta property="og:image:alt" content="A Page" />')
        ->not->toContain('Ignored')
        ->not->toContain('application/ld+json');
});

test('the site layout renders the page meta json-ld nodes as one schema.org graph', function (): void {
    $pageMeta = new PageMeta(new SEOData(title: 'A Page'), [['@type' => 'WebPage', 'name' => 'A Page']]);

    $html = Blade::render('<x-layouts.app :page-meta="$pageMeta">Body</x-layouts.app>', ['pageMeta' => $pageMeta]);

    expect($html)->toContain('{"@context":"https://schema.org","@graph":[{"@type":"WebPage","name":"A Page"}]}');
});
