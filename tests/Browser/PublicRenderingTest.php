<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

test('public pages render accessible typography and focus indicators', function (string $routeName, string $content): void {
    visit(route($routeName))
        ->assertSee($content)
        ->assertScript('document.fonts.check("16px Besley")', true)
        ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(browserDecorativeGlyphCountScript(['✦', '✧', '✨', '🎙️', '🍽️', '🛍️']), 0)
        ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
        ->assertScript($this->missingFocusIndicatorsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
})->with([
    'home' => ['home', 'Disney Parks'],
    'blog' => ['blog.index', 'Blog'],
    'guides' => ['guides.index', 'Guides'],
    'podcast' => ['episodes.index', 'Podcast'],
    'about' => ['about', 'About'],
    'contact' => ['contact.show', 'Contact'],
    'privacy' => ['privacy', 'Privacy information'],
    'search' => ['search', 'Search'],
]);

test('polished discovery and guide artwork remain usable on mobile', function (): void {
    $guide = Guide::factory()->create([
        'category' => 'accessibility',
        'cover_image' => null,
    ]);
    $post = Post::factory()->create();
    Post::factory()->create();

    visit(route('search'))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertSee('Start somewhere inspiring')
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertScript($this->undersizedControlsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    $guidesPage = visit(route('guides.index'))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertSee($guide->title)
        ->assertScript('document.querySelector("[data-guide-artwork]").complete', true)
        ->assertScript('document.querySelector("[data-guide-artwork]").naturalWidth > 0', true)
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    $guidesPage->script('window.scrollTo(0, document.documentElement.scrollHeight)');

    $guidesPage
        ->assertScript('[...document.querySelectorAll("[data-guide-artwork]")].every((image) => image.complete && image.naturalWidth > 0)', true)
        ->assertNoJavaScriptErrors();

    visit(route('blog.show', $post))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertScript('document.querySelector("[data-article-secondary]")', null)
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('published content detail pages have no accessibility issues', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);
    $guide = Guide::factory()->create();

    foreach ([
        'post' => route('blog.show', $post),
        'episode' => route('episodes.show', $episode),
        'guide' => route('guides.show', $guide),
    ] as $url) {
        visit($url)
            ->assertNoAccessibilityIssues()
            ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
            ->assertScript(browserDecorativeGlyphCountScript(['✦', '✧', '✨', '🎙️', '🍽️', '🛍️']), 0)
            ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
            ->assertScript($this->missingFocusIndicatorsScript(), '')
            ->assertNoJavaScriptErrors();
    }
});

test('episode transcript expands accessibly', function (): void {
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);

    visit(route('episodes.show', $episode))
        ->click('Read Full Transcript')
        ->assertScript('document.querySelector("[aria-controls=episode-transcript]").ariaExpanded', 'true')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('mobile navigation opens and remains usable', function (): void {
    visit(route('home'))
        ->on()
        ->mobile()
        ->click('[aria-label="Open navigation menu"]')
        ->assertVisible('#mobile-navigation')
        ->assertScript('document.querySelector("[aria-controls=mobile-navigation]").ariaExpanded', 'true')
        ->click('[aria-label="Close navigation menu"]')
        ->assertScript('getComputedStyle(document.querySelector("#mobile-navigation")).display', 'none')
        ->assertScript('document.querySelector("[aria-controls=mobile-navigation]").ariaExpanded', 'false')
        ->assertNoJavaScriptErrors();
});
