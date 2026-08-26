<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

function contentHorizontalOverflowScript(): string
{
    return <<<'JS'
        (() => document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0)()
        JS;
}

function contentBrokenImageCountScript(): string
{
    return <<<'JS'
        (() => [...document.images].filter((image) => image.complete && image.naturalWidth === 0).length)()
        JS;
}

function contentUndersizedControlsScript(): string
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

test('long public content and portrait artwork stay contained', function (): void {
    $longTitle = str_repeat('AccessibilityResilience', 10);
    $longToken = str_repeat('sensoryfriendlyplanning', 18);
    $portraitPath = 'testing/content-resilience-portrait.webp';
    $portrait = file_get_contents(public_path('images/meet-jeffrey-cassie.webp'));

    if ($portrait === false) {
        throw new RuntimeException('The content resilience artwork fixture could not be loaded.');
    }

    Storage::disk('public')->put(
        $portraitPath,
        $portrait,
    );

    try {
        $post = Post::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-post',
            'excerpt' => "A practical introduction followed by {$longToken}",
            'body' => "## {$longToken}\n\n{$longToken}\n\n[{$longToken}](https://example.com/{$longToken})\n\n`{$longToken}`",
            'cover_image' => $portraitPath,
        ]);
        $guide = Guide::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-guide',
            'excerpt' => "A practical introduction followed by {$longToken}",
            'body' => "## {$longToken}\n\n{$longToken}\n\n[{$longToken}](https://example.com/{$longToken})\n\n`{$longToken}`",
            'cover_image' => $portraitPath,
        ]);
        $episode = Episode::factory()->create([
            'title' => $longTitle,
            'slug' => 'long-content-episode',
            'description' => "A practical introduction followed by {$longToken}",
            'show_notes' => "<h2>{$longToken}</h2><p>{$longToken}</p><p><a href=\"https://example.com/{$longToken}\">{$longToken}</a></p>",
            'transcript' => "<p><strong>Jeffrey:</strong> {$longToken}</p>",
            'cover_image' => $portraitPath,
            'audio_url' => 'https://cdn.example.com/content-resilience.mp3',
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
        ])
            ->on()
            ->mobile()
            ->resize(320, 812);

        $mobilePages->assertScript(contentHorizontalOverflowScript(), 0)
            ->assertScript(contentBrokenImageCountScript(), 0)
            ->assertScript(contentUndersizedControlsScript(), '')
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
        ])->resize(1440, 1000);

        $desktopPages->assertScript(contentHorizontalOverflowScript(), 0)
            ->assertScript(contentBrokenImageCountScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();

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
        Storage::disk('public')->delete($portraitPath);
    }
});

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
    ])
        ->on()
        ->mobile()
        ->resize(320, 812);

    $pages->assertScript(contentHorizontalOverflowScript(), 0)
        ->assertScript(contentUndersizedControlsScript(), '')
        ->assertScript('[...document.querySelectorAll("[aria-current=page]")].some((element) => element.textContent.trim() === "2")', true)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();

    [$blog, $guides, $episodes] = $pages;

    $blog->assertSee('Boundary Post 13');
    $guides->assertSee('Boundary Guide 13');
    $episodes->assertSee('Boundary Episode 13');
});
