<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

function displayPreferenceHorizontalOverflowScript(): string
{
    return <<<'JS'
        (() => document.documentElement.scrollWidth > document.documentElement.clientWidth ? 1 : 0)()
        JS;
}

function activeMotionDurationScript(): string
{
    return <<<'JS'
        (() => {
            const durationInMilliseconds = (duration) => duration.endsWith('ms')
                ? Number.parseFloat(duration)
                : Number.parseFloat(duration) * 1000;

            return [...document.querySelectorAll('*')].filter((element) => {
                const styles = window.getComputedStyle(element);
                const animationDuration = styles.animationDuration.split(',').some((duration) => durationInMilliseconds(duration) > 10);
                const transitionDuration = styles.transitionDuration.split(',').some((duration) => durationInMilliseconds(duration) > 10);

                return animationDuration || transitionDuration;
            }).length;
        })()
        JS;
}

function forcedColorFocusFailuresScript(): string
{
    return <<<'JS'
        (() => {
            const controls = document.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), summary');

            return [...controls].filter((control) => {
                const bounds = control.getBoundingClientRect();

                if (control.closest('[aria-hidden="true"]') || bounds.width === 0 || bounds.height === 0) {
                    return false;
                }

                control.focus();

                const styles = window.getComputedStyle(control);

                return styles.outlineStyle === 'none' || Number.parseFloat(styles.outlineWidth) === 0;
            }).map((control) => {
                const identity = control.id ? `#${control.id}` : control.textContent.trim().replace(/\s+/g, ' ').slice(0, 30);

                return `${control.tagName.toLowerCase()}${identity}`;
            }).join('|');
        })()
        JS;
}

test('public pages reflow with two hundred percent text sizing', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create([
        'audio_url' => 'https://cdn.example.com/display-preferences.mp3',
    ]);

    $pages = visit([
        route('home'),
        route('blog.index'),
        route('guides.index'),
        route('episodes.index'),
        route('about'),
        route('contact.show'),
        route('search', ['q' => 'parks']),
        route('blog.show', $post),
        route('guides.show', $guide),
        route('episodes.show', $episode),
    ])->resize(640, 900);

    [$home, $blog, $guides, $episodes, $about, $contact, $search, $postPage, $guidePage, $episodePage] = $pages;

    foreach ([$home, $blog, $guides, $episodes, $about, $contact, $search, $postPage, $guidePage, $episodePage] as $page) {
        $page->script("document.documentElement.style.fontSize = '200%'");
        $page->assertScript(displayPreferenceHorizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }

    $home->keys('html > body', 'Tab')
        ->assertScript('document.activeElement.textContent.trim()', 'Skip to content')
        ->keys(':focus', 'Enter')
        ->assertScript('document.activeElement.id', 'main-content');
});

test('reduced motion preference removes authored motion', function (): void {
    $page = visit(route('home'), ['reducedMotion' => 'reduce']);

    $page->assertScript("window.matchMedia('(prefers-reduced-motion: reduce)').matches", true)
        ->assertScript('getComputedStyle(document.documentElement).scrollBehavior', 'auto')
        ->assertScript(activeMotionDurationScript(), 0)
        ->assertScript('[...document.querySelectorAll("[data-animate]")].every((element) => getComputedStyle(element).opacity === "1")', true)
        ->assertNoAccessibilityIssues()
        ->assertNoJavaScriptErrors();
});

test('forced colors preserve focus indicators and page structure', function (): void {
    $pages = visit([
        route('home'),
        route('blog.index'),
        route('contact.show'),
    ], ['forcedColors' => 'active'])->resize(1280, 900);

    $pages->assertScript("window.matchMedia('(forced-colors: active)').matches", true)
        ->assertScript(forcedColorFocusFailuresScript(), '')
        ->assertScript(displayPreferenceHorizontalOverflowScript(), 0)
        ->assertNoJavaScriptErrors();
});

test('multilingual and right to left content remains contained', function (): void {
    $multilingualTitle = 'دليل الوصول إلى الحدائق — 東京ディズニーリゾート — Familienfreundliche Planung 🏰✨';
    $multilingualBody = 'تخطيط يوم مريح للعائلة مع فترات راحة حسية. 東京ディズニーリゾートでのアクセシブルな一日。 Crème brûlée, naïve, São Paulo, and Großzügigkeit. 👨‍👩‍👧‍👦🎧';
    $post = Post::factory()->create([
        'title' => $multilingualTitle,
        'slug' => 'multilingual-accessibility-post',
        'excerpt' => $multilingualBody,
        'body' => $multilingualBody,
    ]);
    $guide = Guide::factory()->create([
        'title' => $multilingualTitle,
        'slug' => 'multilingual-accessibility-guide',
        'excerpt' => $multilingualBody,
        'body' => $multilingualBody,
    ]);
    $episode = Episode::factory()->create([
        'title' => $multilingualTitle,
        'slug' => 'multilingual-accessibility-episode',
        'description' => $multilingualBody,
        'show_notes' => "<p>{$multilingualBody}</p>",
        'transcript' => "<p>{$multilingualBody}</p>",
        'audio_url' => 'https://cdn.example.com/multilingual-accessibility.mp3',
    ]);

    $pages = visit([
        route('blog.show', $post),
        route('guides.show', $guide),
        route('episodes.show', $episode),
    ])->resize(320, 812);

    [$postPage, $guidePage, $episodePage] = $pages;

    foreach ([$postPage, $guidePage, $episodePage] as $page) {
        $page->script("document.documentElement.dir = 'rtl'");
        $page->assertSee($multilingualTitle)
            ->assertScript(displayPreferenceHorizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
});
