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

function horizontalOverflowCountScript(): string
{
    return <<<'JS'
        (() => document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0)()
        JS;
}

function undersizedMobileControlsScript(): string
{
    return <<<'JS'
        (() => {
            const controls = document.querySelectorAll([
                'button',
                'input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"])',
                'select',
                'textarea',
                'summary',
                'a[href]',
            ].join(','));

            return [...controls].filter((control) => {
                const styles = window.getComputedStyle(control);
                const bounds = control.getBoundingClientRect();
                const isInlineLink = control.matches('a[href]') && styles.display === 'inline';

                return ! isInlineLink
                    && ! control.closest('[aria-hidden="true"]')
                    && styles.display !== 'none'
                    && styles.visibility !== 'hidden'
                    && bounds.width > 0
                    && bounds.height > 0
                    && (bounds.width < 44 || bounds.height < 44);
            }).map((control) => {
                const bounds = control.getBoundingClientRect();
                const identity = control.id ? `#${control.id}` : control.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                return `${control.tagName.toLowerCase()}${identity} (${Math.round(bounds.width)}x${Math.round(bounds.height)})`;
            }).join('|');
        })()
        JS;
}

function missingFocusIndicatorsScript(): string
{
    return <<<'JS'
        (() => {
            const focusableElements = document.querySelectorAll([
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled]):not([type="hidden"])',
                'select:not([disabled])',
                'textarea:not([disabled])',
                'summary',
                'audio[controls]',
                '[tabindex]:not([tabindex="-1"])',
            ].join(','));

            return [...focusableElements].filter((element) => {
                const bounds = element.getBoundingClientRect();

                if (element.closest('[aria-hidden="true"], details:not([open])') || bounds.width === 0 || bounds.height === 0) {
                    return false;
                }

                element.focus();

                const styles = window.getComputedStyle(element);
                const hasOutline = styles.outlineStyle !== 'none'
                    && styles.outlineColor !== 'rgba(0, 0, 0, 0)'
                    && Number.parseFloat(styles.outlineWidth) > 0;
                const hasBoxShadow = styles.boxShadow !== 'none';

                return ! hasOutline && ! hasBoxShadow;
            }).map((element) => {
                const identity = element.id ? `#${element.id}` : element.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                return `${element.tagName.toLowerCase()}${identity}`;
            }).join('|');
        })()
        JS;
}

test('public page renders without JavaScript errors', function (string $path, string $content): void {
    visit($path)
        ->assertSee($content)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(exposedDecorativeGlyphCountScript(), 0)
        ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
        ->assertScript(missingFocusIndicatorsScript(), '')
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

test('homepage loads its editorial type without the journey tracker', function (): void {
    $page = visit(route('home'));

    $page
        ->assertScript('document.fonts.check("16px Besley")', true)
        ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
        ->assertScript('document.documentElement.classList.contains("js-dispatch-motion")', true)
        ->assertScript('document.documentElement.classList.contains("js-dispatch-journey")', false)
        ->assertScript('document.querySelector("[data-dispatch-journey]")', null)
        ->assertScript('getComputedStyle(document.querySelector("[data-dispatch-motion=hero-paper]")).animationName', 'dispatch-paper-settle')
        ->assertNoJavaScriptErrors();
});

test('interior pages load the shared dispatch typography and arrival motion', function (): void {
    $pages = visit([
        route('blog.index'),
        route('guides.index'),
        route('episodes.index'),
        route('about'),
        route('contact.show'),
        route('search'),
    ]);

    $pages
        ->assertScript('document.fonts.check("16px Besley")', true)
        ->assertScript('getComputedStyle(document.querySelector("h1")).fontFamily.includes("Besley")', true)
        ->assertScript('document.documentElement.classList.contains("js-dispatch-pages")', true)
        ->assertNoJavaScriptErrors();
});

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
        ->assertScript(horizontalOverflowCountScript(), 0)
        ->assertScript(undersizedMobileControlsScript(), '')
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    $guidesPage = visit(route('guides.index'))
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertSee($guide->title)
        ->assertScript('document.querySelector("[data-guide-artwork]").complete', true)
        ->assertScript('document.querySelector("[data-guide-artwork]").naturalWidth > 0', true)
        ->assertScript(horizontalOverflowCountScript(), 0)
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
        ->assertScript(horizontalOverflowCountScript(), 0)
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
            ->assertScript(missingFocusIndicatorsScript(), '')
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

test('desktop navigation identifies only the current destination', function (): void {
    visit(route('about'))
        ->assertScript('document.querySelectorAll(".dispatch-nav-link[aria-current=page]").length', 1)
        ->assertScript('document.querySelector(".dispatch-nav-link[aria-current=page]").textContent.trim()', 'About')
        ->assertScript('document.querySelector(".dispatch-nav-link[href*=blog]").hasAttribute("aria-current")', false)
        ->assertNoJavaScriptErrors();

    visit(route('blog.index'))
        ->assertScript('document.querySelectorAll(".dispatch-nav-link[aria-current=page]").length', 1)
        ->assertScript('document.querySelector(".dispatch-nav-link[aria-current=page]").textContent.trim()', 'Blog')
        ->assertNoJavaScriptErrors();
});

test('keyboard users can skip directly to the main content', function (): void {
    visit(route('home'))
        ->keys('html > body', 'Tab')
        ->assertScript('document.activeElement.textContent.trim()', 'Skip to content')
        ->assertScript('getComputedStyle(document.activeElement).outlineStyle === "none"', false)
        ->keys(':focus', 'Enter')
        ->assertScript('document.activeElement.id', 'main-content')
        ->keys(':focus', 'Tab')
        ->assertScript('document.activeElement.textContent.trim()', 'Read the Blog')
        ->assertNoJavaScriptErrors();
});

test('mobile navigation restores focus when closed with the keyboard', function (): void {
    visit(route('home'))
        ->on()
        ->mobile()
        ->keys('html > body', 'Tab')
        ->keys(':focus', 'Tab')
        ->keys(':focus', 'Tab')
        ->assertScript('document.activeElement.getAttribute("aria-label")', 'Open navigation menu')
        ->keys(':focus', 'Enter')
        ->assertScript('document.querySelector("[aria-controls=mobile-navigation]").ariaExpanded', 'true')
        ->assertVisible('#mobile-navigation')
        ->keys(':focus', 'Tab')
        ->assertScript('document.activeElement.textContent.trim()', 'Home')
        ->keys(':focus', 'Escape')
        ->assertScript('getComputedStyle(document.querySelector("#mobile-navigation")).display', 'none')
        ->assertScript('document.activeElement.getAttribute("aria-label")', 'Open navigation menu')
        ->assertNoJavaScriptErrors();
});

test('search and transcript controls work from the keyboard and unavailable contact remains actionable', function (): void {
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

    visit(route('contact.show'))
        ->assertSee('Email us instead')
        ->assertVisible('.dispatch-letter-form a[href^="mailto:"]')
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
        route('search', ['q' => 'accessible']),
        route('blog.show', $post),
        route('episodes.show', $episode),
        route('guides.show', $guide),
    ])
        ->on()
        ->mobile()
        ->resize(320, 812)
        ->assertScript(horizontalOverflowCountScript(), 0)
        ->assertScript(undersizedMobileControlsScript(), '')
        ->assertScript('document.querySelectorAll(\'[tabindex]:not([tabindex="0"]):not([tabindex="-1"])\').length', 0)
        ->assertScript(missingFocusIndicatorsScript(), '')
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
});

test('admin login renders its authentication controls', function (): void {
    visit('/admin/login')
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertNoJavaScriptErrors();
});

test('article reading time remains a duration while scrolling', function (): void {
    $post = Post::factory()->create([
        'body' => str_repeat('accessible park planning ', 200),
    ]);

    visit(route('blog.show', $post))
        ->assertSee('3 min read')
        ->assertDontSee('1 of 3 min read')
        ->assertNoJavaScriptErrors();
});

test('article navigation respects reduced motion preferences', function (): void {
    $post = Post::factory()->create([
        'body' => "## Planning the day\n\nStart with a flexible plan.\n\n## Finding quiet spaces\n\nTake sensory breaks when needed.",
    ]);

    $page = visit(route('blog.show', $post));

    $page->assertVisible('[data-blog-toc-link][href="#section-0"]')
        ->script(<<<'JS'
            window.matchMedia = () => ({ matches: true });
            Element.prototype.scrollIntoView = function (options) {
                if (options) {
                    window.articleScrollBehavior = options.behavior;
                }
            };
        JS);

    $page->keys('[data-blog-toc-link][href="#section-0"]', 'Enter')
        ->assertScript('window.articleScrollBehavior', 'auto');

    $page->script(<<<'JS'
        Object.defineProperty(navigator, 'clipboard', {
            configurable: true,
            value: {
                writeText: async (value) => {
                    window.copiedArticleUrl = value;
                },
            },
        });
    JS);

    $page->keys('[data-copy-link][aria-label="Copy link"]', 'Enter')
        ->assertScript('window.copiedArticleUrl', route('blog.show', $post))
        ->assertScript('document.querySelector("[data-copy-link][aria-label=\\"Copy link\\"] .copy-feedback").classList.contains("hidden")', false);

    $page->script(<<<'JS'
        window.scrollTo = function (options) {
            window.backToTopScrollBehavior = options.behavior;
        };

        document.getElementById('back-to-top').classList.remove(
            'pointer-events-none',
            'invisible',
            'opacity-0',
        );
    JS);

    $page->click('#back-to-top')
        ->assertScript('window.backToTopScrollBehavior', 'auto')
        ->assertNoJavaScriptErrors();
});
