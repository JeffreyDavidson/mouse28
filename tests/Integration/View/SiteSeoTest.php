<?php

use App\View\SiteSeo;

test('a page keeps its own title, description, robots, type, canonical and share image', function (): void {
    $seo = new SiteSeo()
        ->page(
            title: 'A Page | Mouse28',
            description: 'A page description.',
            robots: 'noindex,follow',
            ogTitle: 'A Page',
            ogType: 'article',
            ogImage: 'https://cdn.example.test/cover.jpg',
            canonical: 'http://localhost/a-page',
        );

    expect($seo)
        ->title->toBe('A Page | Mouse28')
        ->description->toBe('A page description.')
        ->robots->toBe('noindex,follow')
        ->openGraphTitle->toBe('A Page')
        ->type->toBe('article')
        ->image->toBe('https://cdn.example.test/cover.jpg')
        ->url->toBe('http://localhost/a-page')
        ->canonical_url->toBe('http://localhost/a-page')
        ->site_name->toBe('Mouse28')
        ->locale->toBe('')
        ->enableTitleSuffix->toBeFalse();
});

test('a page without its own values shares the logo and its title and points at the current url', function (): void {
    $seo = new SiteSeo()
        ->page(title: 'A Page | Mouse28', description: 'A page description.');

    expect($seo)
        ->robots->toBe('index,follow')
        ->openGraphTitle->toBe('A Page | Mouse28')
        ->type->toBe('website')
        ->image->toBe(url('/images/logo.jpg'))
        ->canonical_url->toBe(url()->current().'/');
});

test('the home page canonical keeps its trailing slash', function (): void {
    $seo = new SiteSeo()
        ->page(title: 'Home', description: 'Home.', canonical: 'https://mouse28.test');

    expect($seo)
        ->url->toBe('https://mouse28.test/')
        ->canonical_url->toBe('https://mouse28.test/');
});

test('a relative share image becomes an absolute url', function (string $image): void {
    $seo = new SiteSeo()
        ->page(title: 'A Page', description: 'A page.', ogImage: $image);

    expect($seo->image)->toBe(url('/images/hero-family.jpg'));
})->with(['with a leading slash' => '/images/hero-family.jpg', 'without one' => 'images/hero-family.jpg']);

test('an error page is not indexed and has no canonical url', function (): void {
    $seo = new SiteSeo()
        ->errorPage(title: 'Page Not Found | Mouse28', description: 'Not found.', ogTitle: 'Mouse28');

    expect($seo)
        ->title->toBe('Page Not Found | Mouse28')
        ->description->toBe('Not found.')
        ->openGraphTitle->toBe('Mouse28')
        ->robots->toBe('noindex, nofollow')
        ->image->toBe(url('/images/logo.jpg'))
        ->url->toBe(url()->current())
        ->canonical_url->toBeNull()
        ->site_name->toBe('Mouse28')
        ->locale->toBe('')
        ->enableTitleSuffix->toBeFalse();
});
