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

function observeBlogCardMotionScript(): string
{
    return <<<'JS'
        (() => {
            window.blogCardMotionCalls = [];

            const animate = Element.prototype.animate;

            Element.prototype.animate = function (keyframes, options) {
                if (this.matches('[data-blog-results] article')) {
                    const frames = Array.from(keyframes);

                    window.blogCardMotionCalls.push({
                        duration: typeof options === 'number' ? options : options?.duration,
                        fromTransform: frames[0]?.transform ?? null,
                        toTransform: frames.at(-1)?.transform ?? null,
                    });
                }

                return animate.call(this, keyframes, options);
            };
        })()
        JS;
}

function blogCardMotionObservedScript(): string
{
    return <<<'JS'
        window.blogCardMotionCalls.some(({ duration, fromTransform, toTransform }) => (
            duration >= 300
            && duration <= 500
            && fromTransform?.startsWith('translate(')
            && fromTransform !== toTransform
        ))
        JS;
}

test('public page renders without JavaScript errors', function (string $routeName, string $content): void {
    visit(route($routeName))
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
    'home' => ['home', 'Disney Parks'],
    'blog' => ['blog.index', 'Blog'],
    'guides' => ['guides.index', 'Guides'],
    'podcast' => ['episodes.index', 'Podcast'],
    'about' => ['about', 'About'],
    'contact' => ['contact.show', 'Contact'],
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

test('blog filters and sorting animate stories without reloading or moving the controls', function (): void {
    Post::factory()->create(['title' => 'Featured park story', 'published_at' => now()]);
    $accessiblePost = Post::factory()->create([
        'title' => 'A quiet entrance plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subWeek(),
    ]);
    $diningPost = Post::factory()->create([
        'title' => 'A family dining review',
        'category' => 'food-reviews',
        'published_at' => now()->subDay(),
    ]);
    Post::factory()->count(7)->create([
        'category' => 'food-reviews',
    ]);

    $page = visit(route('blog.index'));

    $page->assertScript("document.querySelector('[data-blog-status][role=status][aria-live=polite]') !== null", true);
    $page->script(observeBlogCardMotionScript());
    $page->script("document.documentElement.dataset.blogNavigationMarker = 'preserved'; const filters = document.querySelector('[data-blog-filters]'); window.scrollTo({ top: window.scrollY + filters.getBoundingClientRect().top - 120, behavior: 'instant' }); window.blogFilterTop = filters.getBoundingClientRect().top");

    $page
        ->click('a[data-blog-filter-link][href*="park-accessibility"]')
        ->assertQueryStringHas('category', 'park-accessibility')
        ->assertScript('document.title', 'Park Accessibility | Mouse28')
        ->assertSee($accessiblePost->title)
        ->assertDontSee($diningPost->title)
        ->assertScript(blogCardMotionObservedScript(), true)
        ->assertScript('Math.abs(document.querySelector("[data-blog-filters]").getBoundingClientRect().top - window.blogFilterTop) < 2', true);

    $page->script('window.blogCardMotionCalls = []');

    $page
        ->click('a[data-blog-filter-link][href*="food-reviews"]')
        ->assertQueryStringHas('category', 'food-reviews')
        ->assertSee($diningPost->title)
        ->assertDontSee($accessiblePost->title)
        ->assertScript('Math.abs(document.querySelector("[data-blog-filters]").getBoundingClientRect().top - window.blogFilterTop) < 2', true)
        ->click('a[data-blog-filter-link][href*="park-accessibility"]')
        ->assertQueryStringHas('category', 'park-accessibility')
        ->assertScript('document.documentElement.dataset.blogNavigationMarker', 'preserved')
        ->fill('#blog-search', 'quiet entrance')
        ->assertQueryStringHas('q', 'quiet entrance')
        ->assertSee($accessiblePost->title)
        ->assertScript('document.activeElement.id', 'blog-search')
        ->assertScript('document.documentElement.dataset.blogNavigationMarker', 'preserved')
        ->assertScript('document.querySelector("[data-blog-browser]").ariaBusy', 'false')
        ->script('window.blogFilterTop = document.querySelector("[data-blog-filters]").getBoundingClientRect().top');

    $page->script('window.blogCardMotionCalls = []');

    $page
        ->click('a[data-blog-filter-link][href$="/blog"]')
        ->assertQueryStringMissing('category')
        ->assertQueryStringMissing('q')
        ->assertSee($accessiblePost->title)
        ->assertSee($diningPost->title)
        ->assertScript(blogCardMotionObservedScript(), true)
        ->assertScript('Math.abs(document.querySelector("[data-blog-filters]").getBoundingClientRect().top - window.blogFilterTop) < 2', true)
        ->script('window.blogCardMotionCalls = []');

    $page
        ->select('#blog-sort', 'oldest')
        ->assertQueryStringHas('sort', 'oldest')
        ->assertScript('document.querySelector("#blog-sort").value', 'oldest')
        ->assertScript('document.querySelector("[data-blog-results] h3 a").textContent.trim()', $accessiblePost->title)
        ->assertScript(blogCardMotionObservedScript(), true)
        ->assertScript('document.querySelector("#blog-sort").getBoundingClientRect().right - document.querySelector("[data-blog-sort-chevron]").getBoundingClientRect().right >= 15', true)
        ->assertScript('Math.abs(document.querySelector("[data-blog-filters]").getBoundingClientRect().top - window.blogFilterTop) < 2', true)
        ->assertScript('document.documentElement.dataset.blogNavigationMarker', 'preserved')
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('blog filters update without spatial card motion when reduced motion is preferred', function (): void {
    Post::factory()->create([
        'category' => 'food-reviews',
        'published_at' => now(),
    ]);
    Post::factory()->create([
        'category' => 'park-accessibility',
        'published_at' => now()->subDay(),
    ]);
    Post::factory()->create([
        'category' => 'food-reviews',
        'published_at' => now()->subDays(2),
    ]);
    $accessiblePost = Post::factory()->create([
        'category' => 'park-accessibility',
        'published_at' => now()->subDays(3),
    ]);

    $page = visit(route('blog.index'), ['reducedMotion' => 'reduce']);

    $page->assertScript("document.querySelector('[data-blog-status][role=status][aria-live=polite]') !== null", true);
    $page->script(observeBlogCardMotionScript());

    $page
        ->click('a[data-blog-filter-link][href*="park-accessibility"]')
        ->assertQueryStringHas('category', 'park-accessibility')
        ->assertSee($accessiblePost->title)
        ->assertScript('window.blogCardMotionCalls.length', 0)
        ->assertNoJavaScriptErrors();
})->group('browser-smoke', 'browser-compatibility');

test('blog pagination keeps the featured story visible', function (): void {
    $featuredPost = Post::factory()->create([
        'title' => 'Featured throughout the archive',
        'published_at' => now(),
    ]);
    Post::factory()->count(12)->create([
        'published_at' => now()->subDay(),
    ]);

    $page = visit(route('blog.index'));

    $page
        ->click('button[aria-label="Go to page 2"]')
        ->assertQueryStringHas('page', '2')
        ->assertSee($featuredPost->title)
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

test('featured story and viewport stay stable throughout filter transitions', function (): void {
    Post::factory()->create(['title' => 'Permanent featured story', 'published_at' => now()]);
    Post::factory()->create(['category' => 'food-reviews', 'published_at' => now()->subDay()]);
    Post::factory()->count(3)->create(['category' => 'park-accessibility', 'published_at' => now()->subWeek()]);

    $page = visit(route('blog.index'));
    $page->script(<<<'JS'
        window.scrollTo({ top: document.querySelector('[data-blog-filters]').getBoundingClientRect().top + window.scrollY - 120, behavior: 'instant' });
        window.filterSamples = [];
        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-blog-filter-link]')) return;
            const initialY = window.scrollY;
            const initialTop = document.querySelector('[data-blog-filters]').getBoundingClientRect().top;
            const retainedCard = document.querySelector('[data-blog-post]:last-child');
            const started = performance.now();
            const initialCards = [...document.querySelectorAll('[data-blog-post]')].map(card => card.dataset.blogPost).join(',');
            let updatedAt = null;
            window.filterSamples = [];
            const sample = () => {
                window.filterSamples.push({
                    scroll: Math.abs(window.scrollY - initialY),
                    filter: Math.abs(document.querySelector('[data-blog-filters]').getBoundingClientRect().top - initialTop),
                    featured: document.querySelector('.editorial-feature')?.textContent.includes('Permanent featured story') ?? false,
                    moving: [...document.querySelectorAll('[data-blog-post]')].some(card => getComputedStyle(card).transform !== 'none'),
                    cardPosition: [Math.round(retainedCard.getBoundingClientRect().left), Math.round(retainedCard.getBoundingClientRect().top)].join(","),
                });
                const cards = [...document.querySelectorAll('[data-blog-post]')].map(card => card.dataset.blogPost).join(',');
                if (cards !== initialCards && updatedAt === null) updatedAt = performance.now();
                if (performance.now() - started < 10000 && (updatedAt === null || performance.now() - updatedAt < 600)) requestAnimationFrame(sample);
                else window.filterSamplingDone = true;
            };
            window.filterSamplingDone = false;
            requestAnimationFrame(sample);
        }, true);
        JS);

    foreach (['a[data-blog-filter-link][href*="park-accessibility"]', 'a[data-blog-filter-link][href$="/blog"]'] as $selector) {
        $page
            ->click($selector)
            ->assertScript('window.filterSamplingDone', true)
            ->assertScript('window.filterSamples.length > 10', true)
            ->assertScript('window.filterSamples.every(sample => sample.featured && sample.scroll < 2 && sample.filter < 2)', true)
            ->assertScript('window.filterSamples.some(sample => sample.moving)', true)
            ->assertScript('new Set(window.filterSamples.map(sample => sample.cardPosition)).size > 3', true)
            ->assertNoJavaScriptErrors();
    }
})->group('browser-smoke', 'browser-compatibility');
