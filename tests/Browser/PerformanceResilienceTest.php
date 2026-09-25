<?php

use App\Actions\GenerateResponsiveCover;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('mobile visitors receive one preloaded responsive AVIF hero and a lean public script', function (): void {
    $page = visit(route('home'))
        ->on()
        ->mobile()
        ->resize(375, 812);

    $page->assertScript(
        "document.querySelector('.hero-split-photo img').currentSrc.endsWith('/images/hero-family-768.avif')",
        true,
    )->assertScript(
        <<<'JS'
            (() => {
                const preload = document.querySelector('head link[rel="preload"][as="image"]');
                const source = document.querySelector('.hero-split-photo source[type="image/avif"]');

                return preload?.imageSrcset === source.srcset
                    && preload?.imageSizes === source.sizes
                    && preload?.type === 'image/avif'
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
            (() => document.querySelector('.hero-split-photo source[type="image/webp"]') !== null
                && ! performance.getEntriesByType('resource')
                    .some((resource) => resource.name.endsWith('/images/hero-family.webp')))()
            JS,
        true,
    )->assertNoJavaScriptErrors();
});

test('mobile about visitors receive the preloaded responsive safari hero once', function (): void {
    $page = visit(route('about'), [
        'deviceScaleFactor' => 2,
        'viewport' => ['width' => 390, 'height' => 844],
    ]);

    $page->assertScript(
        <<<'JS'
            (() => {
                const image = document.querySelector('[data-about-editorial] header picture img');

                return image?.currentSrc.endsWith('/images/hero-family-768.avif')
                    && image.complete
                    && image.naturalWidth > 0;
            })()
            JS,
        true,
    )->assertScript(
        <<<'JS'
            (() => {
                const preload = document.querySelector('head link[rel="preload"][as="image"]');
                const source = document.querySelector('[data-about-editorial] header picture source[type="image/avif"]');

                return preload?.imageSrcset === source.srcset
                    && preload?.imageSizes === source.sizes
                    && preload?.type === 'image/avif'
                    && preload?.fetchPriority === 'high';
            })()
            JS,
        true,
    )->assertScript(
        'performance.getEntriesByType("resource").filter(resource => resource.name.includes("/images/hero-family")).length',
        1,
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

test('podcast archive artwork uses one suitably sized image download', function (int $density, string $filename): void {
    $page = visit(route('episodes.index'), [
        'deviceScaleFactor' => $density,
        'viewport' => ['width' => 390, 'height' => 844],
    ]);

    expect($page->script('window.devicePixelRatio'))->toBe($density)
        ->and($page->script('document.querySelector(".podcast-cover-frame img").currentSrc'))
        ->toEndWith('/images/podcast/'.$filename)
        ->and($page->script('(() => { const image = document.querySelector(".podcast-cover-frame img"); return image.complete && image.naturalWidth > 0; })()'))
        ->toBeTrue();

    $page->assertScript(
        'performance.getEntriesByType("resource").filter(resource => resource.name.includes("/images/podcast/mouse28-cover")).length',
        1,
    )->assertNoJavaScriptErrors();
})->with([
    '1x display selects the smallest bundled cover' => [1, 'mouse28-cover-640.webp'],
    '2x display selects the medium bundled cover' => [2, 'mouse28-cover-768.webp'],
    '3x display selects the full-resolution cover' => [3, 'mouse28-cover.webp'],
]);

test('mobile blog archive and article load responsive cover artwork without overflowing', function (): void {
    $coverPath = 'posts/browser-responsive-cover-'.Str::uuid().'.webp';
    $cover = file_get_contents(public_path('images/meet-jeffrey-and-cassie.webp'));

    if ($cover === false) {
        throw new RuntimeException('The blog artwork browser fixture could not be loaded.');
    }

    $disk = Storage::disk('public');
    $disk->put($coverPath, $cover);

    $coverHash = hash('sha256', $cover);
    $existingVariants = [];

    foreach (ResponsiveArtwork::WIDTHS as $width) {
        $variantPath = ResponsiveArtwork::variantPath($coverHash, $width);

        if ($disk->exists($variantPath)) {
            $existingVariants[$variantPath] = $disk->get($variantPath);
        }
    }

    try {
        $post = Post::factory()->create([
            'title' => 'Responsive Park Planning',
            'cover_image' => $coverPath,
        ]);

        app(GenerateResponsiveCover::class)($post);

        $viewport = [
            'viewport' => ['width' => 390, 'height' => 844],
            'deviceScaleFactor' => 1,
        ];
        $sourceSelector = 'img[src$="/storage/'.$coverPath.'"]';
        $responsiveArtworkLoadedScript = sprintf(
            <<<'JS'
                (() => {
                    const image = document.querySelector(%s);

                    return image?.complete === true
                        && image.naturalWidth > 0
                        && new URL(image.currentSrc).pathname.includes('/posts/responsive/');
                })()
                JS,
            json_encode($sourceSelector, JSON_THROW_ON_ERROR),
        );

        visit(route('blog.index'), $viewport)
            ->assertSee($post->title)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertScript($responsiveArtworkLoadedScript, true)
            ->assertNoJavaScriptErrors();

        visit(route('blog.show', $post), $viewport)
            ->assertSee($post->title)
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertScript($responsiveArtworkLoadedScript, true)
            ->assertNoJavaScriptErrors();
    } finally {
        $disk->delete($coverPath);

        foreach (ResponsiveArtwork::WIDTHS as $width) {
            $variantPath = ResponsiveArtwork::variantPath($coverHash, $width);
            $disk->delete($variantPath);

            if (isset($existingVariants[$variantPath])) {
                $disk->put($variantPath, $existingVariants[$variantPath]);
            }
        }
    }
})->group('browser-smoke');

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
