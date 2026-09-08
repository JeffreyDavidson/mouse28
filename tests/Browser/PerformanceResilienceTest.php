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
                const preload = document.querySelector('head link[rel="preload"][as="image"]');
                const source = document.querySelector('.hero-split-photo source');

                return preload?.imageSrcset === source.srcset
                    && preload?.imageSizes === source.sizes
                    && preload?.fetchPriority === 'high';
            })()
            JS,
        true,
    )->assertScript(
        'performance.getEntriesByType("resource").filter(resource => resource.name.includes("/images/hero-family")).length',
        1,
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

test('homepage podcast artwork uses one suitably sized image download', function (int $density, string $filename): void {
    $page = visit(route('home'), ['deviceScaleFactor' => $density])
        ->resize(390, 844);

    $page->script('document.querySelector(".dispatch-podcast-frame").scrollIntoView()');

    $page->assertScript(<<<'JS'
        (() => {
            const image = document.querySelector('.dispatch-podcast-frame img');

            return image.complete && image.naturalWidth > 0
                && image.loading === 'lazy';
        })()
        JS, true)
        ->assertScript('document.querySelector(".dispatch-podcast-frame img").currentSrc.endsWith('.json_encode('/images/podcast/'.$filename).')', true)
        ->assertScript('performance.getEntriesByType("resource").filter(resource => resource.name.includes("/images/podcast/mouse28-cover")).length', 1)
        ->assertNoJavaScriptErrors();
})->with([
    '2x display selects the smaller cover' => [2, 'mouse28-cover-640.webp'],
    '3x display retains the full-resolution cover' => [3, 'mouse28-cover.webp'],
]);

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
})->group('browser-smoke');

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
