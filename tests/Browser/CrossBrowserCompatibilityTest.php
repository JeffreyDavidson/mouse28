<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Pest\Browser\Playwright\Playwright;
use Symfony\Component\Process\Process;

use function Pest\Laravel\get;

pest()->browser()->timeout(10000);

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
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
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
            ->assertScript($this->horizontalOverflowScript(), 0)
            ->assertNoJavaScriptErrors();
    }

    visit(route('contact.show'))
        ->assertScript('document.querySelector("#subject").tagName', 'SELECT')
        ->assertScript('document.querySelector("#subject").disabled', false);

    visit(route('episodes.show', $episode))
        ->assertScript('document.querySelector("iframe").src', 'https://share.transistor.fm/e/428d650c')
        ->assertScript('document.querySelector("iframe").height', '180');

    visit(route('blog.show', $post))
        ->assertScript('getComputedStyle(document.querySelector("header")).position', 'sticky')
        ->assertScript('getComputedStyle(document.querySelector("#back-to-top")).position', 'fixed');
})->group('browser-compatibility');

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

    $pages = [];

    foreach ([route('blog.show', $post), route('guides.show', $guide)] as $url) {
        $response = get($url);

        $response->assertOk();
        $pages[] = $response->getContent();
    }

    // Pest 5.0 has no print-media API; use the installed Playwright library on the rendered pages.
    $process = new Process(['node', base_path('tests/Browser/print-media.mjs')], base_path());
    $process->setInput(json_encode([
        'browser' => Playwright::defaultBrowserType()->toPlaywrightName(),
        'pages' => $pages,
        'publicPath' => public_path(),
    ], JSON_THROW_ON_ERROR));
    $process->setTimeout(60);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());
})->group('browser-compatibility');
