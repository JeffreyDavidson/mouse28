<?php

use App\Models\Post;

test('mobile visitors receive the responsive hero and a lean public script', function (): void {
    $page = visit(route('home'))
        ->on()
        ->mobile()
        ->resize(375, 812);

    $page->assertScript(
        "document.querySelector('.hero-split-photo img').currentSrc.endsWith('/images/hero-family-1024.webp')",
        true,
    )->assertScript(
        <<<'JS'
            (() => {
                const script = performance.getEntriesByType('resource')
                    .find((resource) => /\/build\/assets\/app-[^/]+\.js$/.test(resource.name));

                return script !== undefined && script.decodedBodySize < 80000;
            })()
            JS,
        true,
    )->assertScript(
        <<<'JS'
            (() => ! performance.getEntriesByType('resource')
                .some((resource) => resource.name.endsWith('/images/hero-family.webp')))()
            JS,
        true,
    )->assertNoJavaScriptErrors();
});

test('core mobile navigation and search work without JavaScript', function (): void {
    $home = visit(route('home'), ['javaScriptEnabled' => false])
        ->on()
        ->mobile()
        ->resize(375, 812);

    $home->assertVisible('#no-script-navigation')
        ->assertVisible('#no-script-navigation a[href="'.route('blog.index').'"]')
        ->assertVisible('#no-script-navigation a[href="'.route('episodes.index').'"]')
        ->assertVisible('#no-script-navigation a[href="'.route('contact.show').'"]');

    visit(route('search'), ['javaScriptEnabled' => false])
        ->fill('#site-search', 'accessible parks')
        ->keys('#site-search', 'Enter')
        ->assertQueryStringHas('q', 'accessible parks')
        ->assertSee('No results for');
});

test('failed optional artwork preserves content and its reserved layout', function (): void {
    $post = Post::factory()->create([
        'title' => 'Resilient Park Planning',
        'cover_image' => 'posts/missing-artwork.webp',
    ]);

    visit(route('home'))
        ->assertSee($post->title)
        ->assertScript(
            <<<'JS'
                (() => {
                    const image = document.querySelector('img[src$="missing-artwork.webp"]');
                    const bounds = image?.getBoundingClientRect();

                    return image?.complete === true
                        && image.naturalWidth === 0
                        && bounds !== undefined
                        && bounds.height >= 200;
                })()
                JS,
            true,
        )->assertScript('document.querySelector("main").getBoundingClientRect().height > 0', true)
        ->assertNoJavaScriptErrors();
});
