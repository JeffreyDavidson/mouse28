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
