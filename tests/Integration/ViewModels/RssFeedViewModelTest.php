<?php

use App\Models\Category;
use App\Models\Post;
use App\ViewModels\RssFeedViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

covers(RssFeedViewModel::class);

test('blog feed channel takes its title, description and logo from config', function (): void {
    config()->set('seo.site_name', 'Site & Co');
    config()->set('seo.feed_description', 'Notes on <code> & more.');

    $channel = app(RssFeedViewModel::class)->data();

    expect($channel)->toBe([
        'title' => 'Site & Co Blog',
        'link' => route('blog.index'),
        'description' => 'Notes on <code> & more.',
        'feedUrl' => route('rss.blog'),
        'image' => [
            'url' => url('/images/logo.jpg'),
            'title' => 'Site & Co',
            'link' => route('home'),
        ],
        'items' => [],
    ]);
});

test('blog feed items carry each published post title, link, excerpt and date', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'excerpt' => 'A practical planning guide.',
        'published_at' => Date::parse('2026-09-01 10:00:00'),
    ]);

    $item = app(RssFeedViewModel::class)->data()['items'][0];

    expect($item)->toMatchArray([
        'title' => 'Accessible Park Planning',
        'link' => route('blog.show', $post),
        'description' => 'A practical planning guide.',
    ])
        ->and($item['publishedAt']?->toRssString())
        ->toBe('Tue, 01 Sep 2026 10:00:00 +0000');
});

test('blog feed describes a post without an excerpt from its content', function (): void {
    Post::factory()->create([
        'excerpt' => null,
        'content' => '<p>Plan a flexible arrival.</p>',
    ]);

    $item = app(RssFeedViewModel::class)->data()['items'][0];

    expect($item['description'])->toBe('Plan a flexible arrival.');
});

test('blog feed describes a post without an excerpt in plain text from its markdown content', function (?string $excerpt): void {
    Post::factory()->create([
        'excerpt' => $excerpt,
        'content' => "## Arrival\n\n**Plan** a flexible arrival.",
    ]);

    $item = app(RssFeedViewModel::class)->data()['items'][0];

    expect($item['description'])->toBe('Arrival Plan a flexible arrival.');
})->with([
    'missing excerpt' => [null],
    'empty excerpt' => [''],
]);

test('blog feed limits a description from content to 300 characters', function (): void {
    Post::factory()->create([
        'excerpt' => null,
        'content' => str_repeat('a', 301),
    ]);

    $item = app(RssFeedViewModel::class)->data()['items'][0];

    expect($item['description'])->toBe(str_repeat('a', 300).'...');
});

test('blog feed names each post category by its name', function (): void {
    $category = Category::factory()->create(['name' => 'Food & Drink']);
    Post::factory()
        ->for($category)
        ->count(2)
        ->create();

    $items = app(RssFeedViewModel::class)->data()['items'];

    expect(array_column($items, 'category'))->toBe(['Food & Drink', 'Food & Drink']);
});

test('blog feed leaves out the category for an uncategorized post', function (): void {
    Post::factory()->create(['category_id' => null]);

    $items = app(RssFeedViewModel::class)->data()['items'];

    expect($items)->toHaveCount(1)
        ->and($items[0]['category'])
        ->toBeNull();
});
