<?php

use App\Models\Guide;
use App\Models\Post;
use App\Support\SitemapDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(SitemapDocument::class);

test('sitemap includes published content links and excludes unpublished records', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $draftPost = Post::factory()->draft()->create();
    $draftGuide = Guide::factory()->draft()->create();

    $content = app(SitemapDocument::class)->content();

    expect($content)
        ->toContain(route('blog.show', $post))
        ->toContain(route('guides.show', $guide))
        ->not->toContain($draftPost->slug)
        ->not->toContain($draftGuide->slug);
});

test('sitemap omits guide routes and records when guides are disabled', function (): void {
    config()->set('mouse28.guides_enabled', false);
    $guide = Guide::factory()->create();

    $content = app(SitemapDocument::class)->content();

    expect($content)
        ->not->toContain(route('guides.index'))
        ->not->toContain(route('guides.show', $guide));
});
