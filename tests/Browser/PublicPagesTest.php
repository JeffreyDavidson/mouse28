<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

function exposedDecorativeGlyphCountScript(): string
{
    return <<<'JS'
        (() => {
            const glyphs = ['✦', '✧', '✨', '🎙️', '🍽️', '🛍️'];
            const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
            let count = 0;

            while (walker.nextNode()) {
                const parent = walker.currentNode.parentElement;

                if (! parent?.closest('[aria-hidden="true"]') && glyphs.some((glyph) => walker.currentNode.textContent.includes(glyph))) {
                    count++;
                }
            }

            return count;
        })()
        JS;
}

test('public page renders without JavaScript errors', function (string $path, string $content): void {
    visit($path)
        ->assertSee($content)
        ->assertScript('document.fonts.check("16px Besley")', true)
        ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(exposedDecorativeGlyphCountScript(), 0)
        ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
        ->assertScript($this->missingFocusIndicatorsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
})->with([
    'home' => ['/', 'Disney Parks'],
    'blog' => ['/blog', 'Blog'],
    'guides' => ['/guides', 'Guides'],
    'podcast' => ['/episodes', 'Podcast'],
    'about' => ['/about', 'About'],
    'contact' => ['/contact', 'Contact'],
    'search' => ['/search', 'Search'],
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
            ->assertScript(exposedDecorativeGlyphCountScript(), 0)
            ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
            ->assertScript($this->missingFocusIndicatorsScript(), '')
            ->assertNoJavaScriptErrors();
    }

    visit(route('episodes.show', $episode))
        ->click('Read Full Transcript')
        ->assertScript('document.querySelector("[aria-controls=episode-transcript]").ariaExpanded', 'true')
        ->assertNoAccessibilityIssues();
});

test('mobile navigation opens and remains usable', function (): void {
    visit('/')
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

test('blog topics and search update without reloading the page', function (): void {
    $accessiblePost = Post::factory()->create([
        'title' => 'A quiet entrance plan',
        'category' => 'park-accessibility',
    ]);
    $diningPost = Post::factory()->create([
        'title' => 'A family dining review',
        'category' => 'food-reviews',
    ]);

    $page = visit(route('blog.index'));

    $page->assertScript("document.querySelector('body > p[role=status][aria-live=polite]') !== null", true)
        ->script("window.blogNavigationMarker = 'preserved'");

    $page
        ->click('a[data-blog-filter-link][href*="park-accessibility"]')
        ->assertQueryStringHas('category', 'park-accessibility')
        ->assertSee($accessiblePost->title)
        ->assertDontSee($diningPost->title)
        ->assertScript('window.blogNavigationMarker', 'preserved')
        ->fill('#blog-search', 'quiet entrance')
        ->assertQueryStringHas('q', 'quiet entrance')
        ->assertSee($accessiblePost->title)
        ->assertScript('document.activeElement.id', 'blog-search')
        ->assertScript('window.blogNavigationMarker', 'preserved')
        ->assertScript('document.querySelector("[data-blog-browser]").ariaBusy', 'false')
        ->script('window.blogFilterTop = document.querySelector("[data-blog-filters]").getBoundingClientRect().top');

    $page
        ->click('a[data-blog-filter-link][href$="/blog"]')
        ->assertQueryStringMissing('category')
        ->assertQueryStringMissing('q')
        ->assertSee($accessiblePost->title)
        ->assertSee($diningPost->title)
        ->assertScript('Math.abs(document.querySelector("[data-blog-filters]").getBoundingClientRect().top - window.blogFilterTop) < 2', true)
        ->assertScript('window.blogNavigationMarker', 'preserved')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('keyboard users can skip directly to the main content', function (): void {
    visit(route('home'))
        ->keys('html > body', $this->tabKey())
        ->assertScript('document.activeElement.textContent.trim()', 'Skip to content')
        ->assertScript('getComputedStyle(document.activeElement).outlineStyle === "none"', false)
        ->keys(':focus', 'Enter')
        ->assertScript('document.activeElement.id', 'main-content')
        ->keys(':focus', $this->tabKey())
        ->assertScript('document.activeElement.textContent.trim()', 'Read the Blog')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('mobile navigation restores focus when closed with the keyboard', function (): void {
    visit(route('home'))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertScript($this->undersizedControlsScript(), '')
        ->keys('html > body', $this->tabKey())
        ->keys(':focus', $this->tabKey())
        ->keys(':focus', $this->tabKey())
        ->assertScript('document.activeElement.getAttribute("aria-label")', 'Open navigation menu')
        ->keys(':focus', 'Enter')
        ->assertScript('document.querySelector("[aria-controls=mobile-navigation]").ariaExpanded', 'true')
        ->assertVisible('#mobile-navigation')
        ->keys(':focus', $this->tabKey())
        ->assertScript('document.activeElement.textContent.trim()', 'Home')
        ->keys(':focus', 'Escape')
        ->assertScript('getComputedStyle(document.querySelector("#mobile-navigation")).display', 'none')
        ->assertScript('document.activeElement.getAttribute("aria-label")', 'Open navigation menu')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('search and transcript controls work from the keyboard', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'body' => 'Practical accessible planning advice for a Disney parks visit.',
    ]);
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);

    visit(route('search'))
        ->fill('#site-search', 'Accessible Park')
        ->keys('#site-search', 'Enter')
        ->assertQueryStringHas('q', 'Accessible Park')
        ->assertSee($post->title)
        ->assertScript('document.querySelector(".dispatch-interactive-card").tagName', 'A')
        ->assertNoJavaScriptErrors();

    visit(route('episodes.show', $episode))
        ->keys('[aria-controls="episode-transcript"]', 'Space')
        ->assertScript('document.querySelector("[aria-controls=episode-transcript]").ariaExpanded', 'true')
        ->assertSee('Collapse Transcript')
        ->assertNoJavaScriptErrors();

});

test('public pages remain usable at mobile widths', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'body' => "## Planning the day\n\nStart with a flexible plan.\n\n## Finding quiet spaces\n\nTake sensory breaks when needed.",
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Accessible Disney Travel',
        'description' => 'A conversation about accessible Disney travel.',
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);
    $guide = Guide::factory()->create([
        'title' => 'Accessible Parks Guide',
        'excerpt' => 'An accessible guide for planning a parks visit.',
    ]);

    visit([
        route('home'),
        route('blog.index'),
        route('guides.index'),
        route('episodes.index'),
        route('about'),
        route('contact.show'),
        route('blog.show', $post),
        route('episodes.show', $episode),
        route('guides.show', $guide),
    ])
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertScript($this->horizontalOverflowScript(), 0)
        ->assertScript($this->undersizedControlsScript(), '')
        ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
        ->assertScript($this->missingFocusIndicatorsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('mobile search and transcript controls remain usable', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'body' => 'Practical accessible planning advice for a Disney parks visit.',
    ]);
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);

    visit(route('search'))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->fill('#site-search', 'Accessible Park')
        ->click('form[role="search"] button[type="submit"]')
        ->assertQueryStringHas('q', 'Accessible Park')
        ->assertSee($post->title)
        ->assertNoJavaScriptErrors();

    visit(route('episodes.show', $episode))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->click('Read Full Transcript')
        ->assertScript('document.querySelector("[aria-controls=episode-transcript]").ariaExpanded', 'true')
        ->assertSee('Collapse Transcript')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('article navigation scrolls to headings and back to the top with reduced motion', function (): void {
    $post = Post::factory()->create([
        'body' => "## Planning the day\n\n".str_repeat("Flexible park planning and sensory breaks.\n\n", 80)."## Finding quiet spaces\n\nTake a break.",
    ]);
    $page = visit(route('blog.show', $post), ['reducedMotion' => 'reduce']);

    $page->assertScript("window.matchMedia('(prefers-reduced-motion: reduce)').matches", true)
        ->assertAttribute('#back-to-top', 'aria-hidden', 'true')
        ->keys('[data-blog-toc-link][href="#section-1"]', 'Enter')
        ->assertScript('window.scrollY > 500', true)
        ->assertScript('Math.abs(document.querySelector("#section-1").getBoundingClientRect().top) < 200', true)
        ->assertAttribute('#back-to-top', 'aria-hidden', 'false')
        ->assertAttribute('#back-to-top', 'tabindex', '0')
        ->click('#back-to-top')
        ->assertScript('window.scrollY', 0)
        ->assertAttribute('#back-to-top', 'aria-hidden', 'true')
        ->assertAttribute('#back-to-top', 'tabindex', '-1')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke');

test('copying an article link gives accessible feedback', function (): void {
    $post = Post::factory()->create();
    $page = visit(route('blog.show', $post));

    $page->script(<<<'JS'
        Object.defineProperty(navigator, 'clipboard', {
            configurable: true,
            value: { writeText: async (value) => { window.copiedArticleUrl = value; } },
        });
        JS);

    $page->keys('[data-copy-link][aria-label="Copy link"]', 'Enter')
        ->assertScript('window.copiedArticleUrl', route('blog.show', $post))
        ->assertSee('Copied!')
        ->assertNoJavaScriptErrors();
});

test('accessibility checks reject small targets and misleading focus decoration', function (): void {
    $page = visit(route('home'));

    $page->script(<<<'JS'
        document.body.innerHTML = `
            <button id="small-target" style="width:46px;height:46px;min-height:0;padding:0">Small</button>
            <button id="static-shadow" style="outline:none;box-shadow:0 0 2px black">Shadow</button>
            <div inert><button id="cannot-focus" style="outline:2px solid black">Inert</button></div>
        `;
        JS);

    expect($page->script($this->undersizedControlsScript()))->toContain('button#small-target (46x46)')
        ->and($page->script($this->missingFocusIndicatorsScript()))->toContain('button#static-shadow', 'button#cannot-focus');
});
