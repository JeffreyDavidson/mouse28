<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

pest()->browser()->timeout(10000);

function crossBrowserHorizontalOverflowScript(): string
{
    return 'document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0';
}

function activatePrintMediaScript(): string
{
    return <<<'JS'
        (() => {
            let activatedRules = 0;

            const activateRules = (rules) => {
                [...rules].forEach((rule) => {
                    if (rule.media?.mediaText?.split(',').some((medium) => medium.trim() === 'print')) {
                        rule.media.mediaText = 'all';
                        activatedRules++;
                    }

                    if (rule.cssRules) {
                        activateRules(rule.cssRules);
                    }
                });
            };

            [...document.styleSheets].forEach((styleSheet) => activateRules(styleSheet.cssRules));

            return activatedRules;
        })()
        JS;
}

test('public reading and form surfaces work across supported browsers', function (): void {
    config()->set('services.turnstile.site_key', '1x00000000000000000000AA');
    config()->set('services.turnstile.secret_key', '1x0000000000000000000000000000000AA');

    $post = Post::factory()->create([
        'title' => 'Cross-Browser Park Planning',
        'body' => "## Arrival\n\nPlan a flexible arrival.\n\n## Sensory breaks\n\nSchedule time to reset.",
    ]);
    $guide = Guide::factory()->create([
        'title' => 'Cross-Browser Accessibility Guide',
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Cross-Browser Podcast Episode',
        'audio_url' => 'https://cdn.example.com/cross-browser.mp3',
    ]);

    foreach ([
        route('home') => 'Disney Parks',
        route('blog.index') => 'Blog',
        route('guides.index') => 'Guides',
        route('episodes.index') => 'Podcast',
        route('contact.show') => 'Contact',
        route('blog.show', $post) => $post->title,
        route('guides.show', $guide) => $guide->title,
        route('episodes.show', $episode) => $episode->title,
    ] as $url => $content) {
        visit($url)
            ->resize(1280, 900)
            ->assertSee($content)
            ->assertScript(crossBrowserHorizontalOverflowScript(), 0)
            ->assertNoJavaScriptErrors();
    }

    visit(route('contact.show'))
        ->assertScript('document.querySelector("#subject").tagName', 'SELECT')
        ->assertScript('document.querySelector("#subject").disabled', false);

    visit(route('episodes.show', $episode))
        ->assertScript('document.querySelector("audio").controls', true)
        ->assertScript('document.querySelector("audio").preload', 'metadata');

    visit(route('blog.show', $post))
        ->assertScript('getComputedStyle(document.querySelector("header")).position', 'sticky')
        ->assertScript('getComputedStyle(document.querySelector("#back-to-top")).position', 'fixed');
});

test('articles and guides provide a focused print presentation', function (): void {
    $externalUrl = 'https://example.com/accessible-planning';
    $post = Post::factory()->create([
        'title' => 'Printable Park Planning',
        'body' => "## Before You Go\n\nRead the [official planning details]({$externalUrl}).",
    ]);
    $guide = Guide::factory()->create([
        'title' => 'Printable Accessibility Guide',
        'body' => "## Before You Go\n\nRead the [official planning details]({$externalUrl}).",
    ]);

    foreach ([route('blog.show', $post), route('guides.show', $guide)] as $url) {
        $page = visit($url);

        $page->assertScript(activatePrintMediaScript(), 1)
            ->assertScript('getComputedStyle(document.querySelector("header")).display', 'none')
            ->assertScript('getComputedStyle(document.querySelector("footer")).display', 'none')
            ->assertScript('[...document.querySelectorAll("[data-print-hidden]")].every((element) => getComputedStyle(element).display === "none")', true)
            ->assertScript('getComputedStyle(document.querySelector(".blog-article-content")).maxWidth', 'none')
            ->assertScript(
                <<<'JS'
                    (() => {
                        const content = getComputedStyle(document.querySelector('.blog-article-content a'), '::after').content;

                        return content.includes('https://example.com/accessible-planning') || content.includes('attr(href)');
                    })()
                    JS,
                true,
            )
            ->assertNoJavaScriptErrors();
    }
});
