<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

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
        $page->assertScript($this->horizontalOverflowScript(), 0)
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
        ->assertScript($this->missingFocusIndicatorsScript(), '')
        ->assertScript($this->horizontalOverflowScript(), 0)
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
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoAccessibilityIssues()
            ->assertNoJavaScriptErrors();
    }
});
