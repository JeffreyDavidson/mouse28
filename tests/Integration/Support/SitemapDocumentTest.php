<?php

use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Support\SitemapDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(SitemapDocument::class);

test('sitemap includes published content links and excludes unpublished records', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $draftPost = Post::factory()
        ->draft()
        ->create();
    $draftGuide = Guide::factory()
        ->draft()
        ->create();

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

test('sitemap lists the newsletter archive and only live issues', function (): void {
    $live = NewsletterIssue::factory()->create();
    $draft = NewsletterIssue::factory()
        ->draft()
        ->create();
    $scheduled = NewsletterIssue::factory()
        ->scheduled()
        ->create();

    $content = app(SitemapDocument::class)->content();

    expect($content)
        ->toContain(route('newsletter.index'))
        ->toContain(route('newsletter.issue', $live))
        ->not->toContain($draft->slug)
        ->not->toContain($scheduled->slug);
});

test('sitemap entries break publish-time ties by id so their order is stable on MySQL', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create();
    Guide::factory()->create();
    NewsletterIssue::factory()->create();

    $orderings = publishTimeOrderings(fn () => app(SitemapDocument::class)->content());

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
