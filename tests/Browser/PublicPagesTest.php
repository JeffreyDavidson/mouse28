<?php

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
        ->click('[aria-label="Toggle navigation menu"]')
        ->assertVisible('#mobile-navigation')
        ->assertNoJavaScriptErrors();
});

test('admin login renders its authentication controls', function (): void {
    visit('/admin/login')
        ->assertVisible('input[type="email"]')
        ->assertVisible('input[type="password"]')
        ->assertNoJavaScriptErrors();
});
