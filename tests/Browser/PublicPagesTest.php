<?php

use App\Models\Post;

test('public page renders without JavaScript errors', function (string $path, string $content): void {
    visit($path)
        ->assertSee($content)
        ->assertNoJavaScriptErrors();
})->with([
    'home' => ['/', 'Disney Parks'],
    'blog' => ['/blog', 'Blog'],
    'guides' => ['/guides', 'Guides'],
    'podcast' => ['/episodes', 'Podcast'],
    'about' => ['/about', 'About'],
    'contact' => ['/contact', 'Contact'],
]);

test('mobile navigation opens and remains usable', function (): void {
    visit('/')
        ->on()
        ->mobile()
        ->click('[aria-label="Open navigation menu"]')
        ->assertVisible('#mobile-navigation')
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
