<?php

use App\Models\Category;
use App\Models\Post;
use App\Support\BlogRssFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(BlogRssFeed::class);

test('blog feed includes published metadata and excludes unpublished posts', function (): void {
    $published = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'excerpt' => 'A practical planning guide.',
    ]);
    $draft = Post::factory()
        ->draft()
        ->create(['title' => 'Draft Park Planning']);
    $scheduled = Post::factory()
        ->scheduled()
        ->create(['title' => 'Scheduled Park Planning']);

    $content = app(BlogRssFeed::class)->content();

    expect($content)
        ->toContain('<title>Accessible Park Planning</title>')
        ->toContain('<description>A practical planning guide.</description>')
        ->toContain(route('blog.show', $published))
        ->not->toContain($draft->title)
        ->not->toContain($scheduled->title);
});

test('blog feed limits entries to the newest twenty published posts', function (): void {
    $oldest = Post::factory()->create(['published_at' => now()->subDays(30)]);
    Post::factory()
        ->count(20)
        ->create(['published_at' => now()->subDays(2)]);

    $content = app(BlogRssFeed::class)->content();

    expect(substr_count($content, '<item>'))->toBe(20)
        ->and($content)
        ->not->toContain($oldest->title);
});

test('blog feed describes a post without an excerpt from its content', function (): void {
    Post::factory()->create([
        'excerpt' => null,
        'content' => '<p>Plan a flexible arrival.</p>',
    ]);

    $content = app(BlogRssFeed::class)->content();

    expect($content)->toContain('<description>Plan a flexible arrival.</description>');
});

test('blog feed names each post category by its escaped name', function (): void {
    $category = Category::factory()->create(['name' => 'Food & Drink']);
    Post::factory()
        ->for($category)
        ->count(2)
        ->create();

    $content = app(BlogRssFeed::class)->content();

    expect(substr_count($content, '<category>Food &amp; Drink</category>'))->toBe(2);
});

test('blog feed leaves out the category element for an uncategorized post', function (): void {
    Post::factory()->create(['category_id' => null]);

    $content = app(BlogRssFeed::class)->content();

    expect($content)->toContain('<item>')
        ->not->toContain('<category>');
});

test('blog feed describes a post without an excerpt in plain text from its markdown content', function (?string $excerpt): void {
    Post::factory()->create([
        'excerpt' => $excerpt,
        'content' => "## Arrival\n\n**Plan** a flexible arrival.",
    ]);

    $content = app(BlogRssFeed::class)->content();

    expect($content)->toContain('<description>Arrival Plan a flexible arrival.</description>');
})->with([
    'missing excerpt' => [null],
    'empty excerpt' => [''],
]);

test('blog feed breaks publish-time ties by id so their order is stable on MySQL', function (): void {
    Post::factory()
        ->count(2)
        ->create();

    $orderings = publishTimeOrderings(fn () => app(BlogRssFeed::class)->content());

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
