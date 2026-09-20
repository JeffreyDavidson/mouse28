<?php

use function Pest\Laravel\get;

test('robots policies keep private and generated routes out of crawlers', function (): void {
    $response = get(route('robots'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('User-agent: *')
        ->assertSee('Allow: /')
        ->assertSee('Disallow: /admin')
        ->assertSee('Disallow: /preview/')
        ->assertSee('Disallow: /search')
        ->assertSee('Sitemap: '.route('sitemap'));

    expect($response->getContent())->toContain(
        'Sitemap: '.route('sitemap'),
    );
});
