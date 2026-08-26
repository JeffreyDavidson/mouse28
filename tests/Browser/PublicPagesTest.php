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

test('public page renders without JavaScript errors', function (string $path, string $content): void {
    visit($path)
        ->assertSee($content)
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(exposedDecorativeGlyphCountScript(), 0)
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

test('published content detail pages have no accessibility issues', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/accessible-episode.mp3',
    ]);
    $guide = Guide::factory()->create();

    $pages = visit([
        route('blog.show', $post),
        route('episodes.show', $episode),
        route('guides.show', $guide),
    ]);

    $pages->assertNoAccessibilityIssues()
        ->assertScript('document.querySelectorAll(\'svg:not([aria-hidden="true"]):not([aria-label]):not([aria-labelledby]):not(:has(title))\').length', 0)
        ->assertScript(exposedDecorativeGlyphCountScript(), 0)
        ->assertNoJavaScriptErrors();

    [, $episodePage] = $pages;

    $episodePage->click('Read Full Transcript')
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

    $page->click('[data-blog-toc-link][href="#section-0"]')
        ->assertScript('window.articleScrollBehavior', 'auto');

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
