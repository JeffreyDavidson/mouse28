<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;

function homepageStoryColumnsDoNotOverlapScript(): string
{
    return <<<'JS'
        (() => {
            const feature = document.querySelector('.dispatch-feature-book')?.getBoundingClientRect();
            const latest = document.querySelector('.dispatch-latest-sheet')?.getBoundingClientRect();

            if (! feature || ! latest) {
                return false;
            }

            return window.innerWidth < 1536
                ? feature.bottom <= latest.top + 1
                : feature.right <= latest.left + 1;
        })()
        JS;
}

function contentBrokenImageCountScript(): string
{
    return <<<'JS'
        (() => [...document.images].filter((image) => image.complete && image.naturalWidth === 0).length)()
        JS;
}

function homepagePodcastContentIsContainedScript(): string
{
    return <<<'JS'
        (() => {
            const artwork = document.querySelector('.dispatch-podcast-frame img');
            const content = document.querySelector('.dispatch-podcast-frame')?.nextElementSibling;
            const title = content?.querySelector('h3');

            return artwork !== null
                && artwork.getBoundingClientRect().width >= 100
                && title instanceof HTMLElement
                && title.scrollWidth <= title.clientWidth + 1;
        })()
        JS;
}

test('homepage story columns stay separated at responsive breakpoints', function (): void {
    Post::factory()
        ->count(4)
        ->create();

    $page = visit(route('home'));

    foreach ([[320, 812], [768, 900], [1024, 900], [1280, 900], [1440, 1000], [1600, 1000]] as [$width, $height]) {
        $page->resize($width, $height)
            ->assertScript(homepageStoryColumnsDoNotOverlapScript(), true);
    }
});

test('on wide screens the featured story lines up with the latest stories and clears the next section', function (): void {
    // Arrange
    Post::factory()
        ->count(4)
        ->create();

    // Act
    $page = visit(route('home'))->resize(1600, 1000);

    // Assert: once the section's reveal settles, the card's top is level with the Latest Stories
    // panel (within its 2-degree tilt) and its bottom stays above the section that follows.
    $page->assertScript(browserWaitForScript(<<<'JS'
        (() => {
            const cluster = document.querySelector('.dispatch-blog-cluster');
            const feature = document.querySelector('.dispatch-feature-book')?.getBoundingClientRect();
            const latest = document.querySelector('.dispatch-latest-sheet')?.getBoundingClientRect();
            const next = cluster?.nextElementSibling?.getBoundingClientRect();

            return Boolean(feature && latest && next)
                && Math.abs(feature.top - latest.top) <= 24
                && feature.bottom <= next.top;
        })()
        JS), true);
});

test('below 1280 the hero photo sits under the introduction in a wide frame', function (): void {
    // Arrange
    $page = visit(route('home'));

    foreach ([768, 1024] as $width) {
        // Act
        $page->resize($width, 900);

        // Assert: offset sizes ignore the frame's slight tilt, so the ratio is the frame's own.
        $page->assertScript(<<<'JS'
            (() => {
                const paper = document.querySelector('[data-dispatch-motion="hero-paper"]');
                const photo = document.querySelector('.hero-split-photo');
                const ratio = photo.offsetWidth / photo.offsetHeight;

                return photo.offsetTop >= paper.offsetTop + paper.offsetHeight
                    && ratio > 1.7
                    && ratio < 1.8;
            })()
            JS, true);
    }
});

test('from 1280 the hero photo sits beside the introduction', function (): void {
    // Arrange
    $page = visit(route('home'));

    // Act
    $page->resize(1280, 900);

    // Assert
    $page->assertScript(<<<'JS'
        (() => {
            const paper = document.querySelector('[data-dispatch-motion="hero-paper"]');
            const photo = document.querySelector('.hero-split-photo');

            return photo.offsetLeft >= paper.offsetLeft + paper.offsetWidth
                && photo.offsetTop === paper.offsetTop;
        })()
        JS, true);
});

test('on tablets and laptops the featured story sits beside its image and clamps a long title to four lines', function (): void {
    // Arrange
    Post::factory()->create([
        'title' => 'What changed when we stopped trying to do everything: a slower, sensory-friendly week at Walt Disney World with our family and friends',
    ]);
    $page = visit(route('home'));

    foreach ([768, 1024, 1440] as $width) {
        // Act
        $page->resize($width, 900);

        // Assert
        $page->assertScript(<<<'JS'
            (() => {
                const book = document.querySelector('.dispatch-feature-book');
                const image = book.querySelector('[data-post-artwork-fallback], img').getBoundingClientRect();
                const title = book.querySelector('.dispatch-book-title p.font-heading');
                const text = title.getBoundingClientRect();
                const lines = Math.round(title.clientHeight / parseFloat(getComputedStyle(title).lineHeight));

                return image.right <= text.left
                    && lines === 4
                    && title.scrollHeight > title.clientHeight;
            })()
            JS, true);
    }
});

test('on the widest screens the featured story keeps its book layout without the summary', function (): void {
    // Arrange
    Post::factory()->create([
        'excerpt' => 'Shaded paths and low-noise benches we use when the day gets loud.',
    ]);

    // Act
    $page = visit(route('home'))->resize(1600, 1000);

    // Assert
    $page->assertScript(<<<'JS'
        (() => {
            const book = document.querySelector('.dispatch-feature-book');
            const title = book.querySelector('.dispatch-book-title');
            const frame = book.querySelector('.dispatch-book-title + div');

            // Offsets ignore the book's tilt, so the title is measured as sitting above the image.
            return title.offsetTop + title.offsetHeight <= frame.offsetTop
                && book.innerText.includes('Read the story') === false
                && book.innerText.includes('Shaded paths') === false;
        })()
        JS, true);
});

test('long public content and portrait artwork stay contained', function (): void {
    $longTitle = str_repeat('AccessibilityResilience', 10);
    $longToken = str_repeat('sensoryfriendlyplanning', 18);
    $portraitPath = 'testing/content-resilience-portrait.webp';
    $portrait = file_get_contents(public_path('images/meet-jeffrey-cassie.webp'));

    if ($portrait === false) {
        throw new RuntimeException('The content resilience artwork fixture could not be loaded.');
    }

    // The responsive variants are linked with absolute URLs, so point them at the test server.
    config(['filesystems.disks.public.url' => url('/storage')]);
    Storage::forgetDisk('public');

    Storage::disk('public')->put(
        $portraitPath,
        $portrait,
    );

    try {
        $post = Post::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-post',
            'excerpt' => "A practical introduction followed by {$longToken}",
            'content' => "## {$longToken}\n\n{$longToken}\n\n[{$longToken}](https://example.com/{$longToken})\n\n`{$longToken}`",
            'featured_image_path' => $portraitPath,
        ]);
        $guide = Guide::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-guide',
            'excerpt' => "A practical introduction followed by {$longToken}",
            'content' => "## {$longToken}\n\n{$longToken}\n\n[{$longToken}](https://example.com/{$longToken})\n\n`{$longToken}`",
            'featured_image_path' => $portraitPath,
        ]);
        $episode = Episode::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-episode',
            'description' => "A practical introduction followed by {$longToken}",
            'show_notes' => "<h2>{$longToken}</h2><p>{$longToken}</p><p><a href=\"https://example.com/{$longToken}\">{$longToken}</a></p>",
            'transcript' => "<p><strong>Jeffrey:</strong> {$longToken}</p>",
            'featured_image_path' => $portraitPath,
        ]);

        $mobilePages = visit([
            route('home'),
            route('blog.index'),
            route('guides.index'),
            route('episodes.index'),
            route('search', ['q' => 'AccessibilityResilience']),
            route('blog.show', $post),
            route('guides.show', $guide),
            route('episodes.show', $episode),
        ]);
        $mobilePages->on()
            ->mobile()
            ->resize(320, 812);

        $mobilePages->assertScript($this->horizontalOverflowScript(), 0)
            ->assertScript(contentBrokenImageCountScript(), 0)
            ->assertScript($this->undersizedControlsScript(), '')
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();

        $desktopPages = visit([
            route('home'),
            route('blog.index'),
            route('guides.index'),
            route('episodes.index'),
            route('search', ['q' => 'AccessibilityResilience']),
            route('blog.show', $post),
            route('guides.show', $guide),
            route('episodes.show', $episode),
        ]);
        $desktopPages->resize(1440, 1000);

        $desktopPages->assertScript($this->horizontalOverflowScript(), 0)
            ->assertScript(contentBrokenImageCountScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();

        [$desktopHome] = $desktopPages;

        $desktopHome->assertScript(homepagePodcastContentIsContainedScript(), true);

        [, , $guideIndex, , , $postPage, , $episodePage] = $mobilePages;

        $guideIndex->assertScript(
            'getComputedStyle(document.querySelector(\'img[src$="content-resilience-portrait.webp"]\')).objectFit',
            'cover',
        );
        $postPage->assertScript(
            'getComputedStyle(document.querySelector(\'img[src$="content-resilience-portrait.webp"]\')).objectFit',
            'cover',
        );
        $episodePage->assertScript(
            'getComputedStyle(document.querySelector(\'img[src$="content-resilience-portrait.webp"]\')).objectFit',
            'cover',
        );
    } finally {
        // The model observers generated variants for the shared fixture path.
        app(ResponsiveImageVariants::class)->delete($portraitPath);
        Storage::disk('public')->delete($portraitPath);
    }
})->group('browser-smoke');

test('pagination boundaries stay usable on narrow screens', function (): void {
    foreach (range(1, 13) as $number) {
        Post::factory()->create([
            'title' => "Boundary Post {$number}",
            'slug' => "boundary-post-{$number}",
            'published_at' => now()->subMinutes($number),
        ]);
        Guide::factory()->create([
            'title' => "Boundary Guide {$number}",
            'slug' => "boundary-guide-{$number}",
            'published_at' => now()->subMinutes($number),
        ]);
        Episode::factory()->create([
            'title' => "Boundary Episode {$number}",
            'slug' => "boundary-episode-{$number}",
            'episode_number' => $number,
            'published_at' => now()->subMinutes($number),
        ]);
    }

    $pages = visit([
        route('blog.index', ['page' => 2]),
        route('guides.index', ['page' => 2]),
        route('episodes.index', ['page' => 2]),
        route('search', ['q' => 'Boundary', 'postsPage' => 2, 'guidesPage' => 2, 'episodesPage' => 2]),
    ]);
    $pages->on()
        ->mobile()
        ->resize(320, 812);

    $pages->assertScript($this->horizontalOverflowScript(), 0)
        ->assertScript($this->undersizedControlsScript(), '')
        ->assertScript('[...document.querySelectorAll("[aria-current=page]")].some((element) => element.textContent.trim() === "2")', true)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    [$blog, $guides, $episodes, $search] = $pages;

    $blog->assertSee('Boundary Post 13');
    $guides->assertSee('Boundary Guide 13');
    $episodes->assertSee('Boundary Episode 13');
    $search->assertSee('Boundary Post 7')
        ->assertSee('Boundary Guide 7')
        ->assertSee('Boundary Episode 7');
});
