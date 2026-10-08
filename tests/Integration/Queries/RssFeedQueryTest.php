<?php

use App\Models\Post;
use App\Queries\RssFeedQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(RssFeedQuery::class);

test('blog feed query returns published posts and excludes unpublished posts', function (): void {
    $published = Post::factory()->create(['title' => 'Accessible Park Planning']);
    Post::factory()
        ->draft()
        ->create(['title' => 'Draft Park Planning']);
    Post::factory()
        ->scheduled()
        ->create(['title' => 'Scheduled Park Planning']);

    $posts = app(RssFeedQuery::class)->get();

    expect($posts->modelKeys())->toBe([$published->id]);
});

test('blog feed query limits entries to the newest twenty published posts', function (): void {
    $oldest = Post::factory()->create(['published_at' => now()->subDays(30)]);
    Post::factory()
        ->count(20)
        ->create(['published_at' => now()->subDays(2)]);

    $posts = app(RssFeedQuery::class)->get();

    expect($posts)->toHaveCount(20)
        ->and($posts->modelKeys())
        ->not->toContain($oldest->id);
});

test('blog feed query holds the configured number of newest posts', function (): void {
    config()->set('mouse28.blog_feed_items', 2);
    $oldest = Post::factory()->create(['published_at' => now()->subDays(9)]);
    $newer = Post::factory()->create(['published_at' => now()->subDays(2)]);
    $newest = Post::factory()->create(['published_at' => now()->subDay()]);

    $posts = app(RssFeedQuery::class)->get();

    expect($posts->modelKeys())->toBe([$newest->id, $newer->id])
        ->not->toContain($oldest->id);
});

test('blog feed query reads only the columns the feed shows and the category name', function (): void {
    Post::factory()->create();

    $post = app(RssFeedQuery::class)
        ->get()
        ->sole();

    expect(array_keys($post->getAttributes()))->toBe(['id', 'slug', 'title', 'excerpt', 'content', 'published_at', 'category_id'])
        ->and(array_keys($post->category?->getAttributes() ?? []))
        ->toBe(['id', 'name']);
});

test('blog feed query breaks publish-time ties by id so their order is stable on MySQL', function (): void {
    Post::factory()
        ->count(2)
        ->create();

    $orderings = publishTimeOrderings(fn () => app(RssFeedQuery::class)->get());

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
