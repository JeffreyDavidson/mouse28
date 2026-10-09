<?php

use App\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

test('page meta holds the seo data and the page json-ld nodes', function (): void {
    $seo = new SEOData(title: 'A Page');
    $nodes = [['@type' => 'WebPage', 'name' => 'A Page']];

    $pageMeta = new PageMeta($seo, $nodes);

    expect($pageMeta)
        ->seo->toBe($seo)
        ->structuredData->toBe($nodes);
});

test('page meta has no json-ld nodes by default', function (): void {
    expect(new PageMeta(new SEOData)->structuredData)->toBeEmpty();
});
