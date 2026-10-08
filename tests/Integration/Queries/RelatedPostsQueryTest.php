<?php

use App\Models\Category;
use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(RelatedPostsQuery::class);

beforeEach(function (): void {
    $this->freezeSecond();
});

test('related posts prioritize category and fill remaining slots with recent posts', function (): void {
    $category = Category::factory()->create();
    $current = Post::factory()
        ->for($category)
        ->create();
    $olderMatch = Post::factory()
        ->for($category)
        ->create([
            'published_at' => now()->subDays(4),
        ]);
    $newerMatch = Post::factory()
        ->for($category)
        ->create([
            'published_at' => now()->subDays(3),
        ]);
    $fallback = Post::factory()->create([
        'published_at' => now()->subDay(),
    ]);
    Post::factory()
        ->for($category)
        ->draft()
        ->create();
    Post::factory()
        ->for($category)
        ->scheduled()
        ->create();

    $related = app(RelatedPostsQuery::class)->get($current, 3);

    expect($related->modelKeys())->toBe([$newerMatch->id, $olderMatch->id, $fallback->id]);
});

test('related posts respect the requested limit', function (int $limit): void {
    $category = Category::factory()->create();
    $current = Post::factory()
        ->for($category)
        ->create();
    $newer = Post::factory()
        ->for($category)
        ->create(['published_at' => now()->subDay()]);
    $older = Post::factory()
        ->for($category)
        ->create(['published_at' => now()->subDays(2)]);
    Post::factory()->create(['published_at' => now()->subDays(3)]);

    $related = app(RelatedPostsQuery::class)->get($current, $limit);

    expect($related->modelKeys())->toBe(array_slice([$newer->id, $older->id], 0, $limit));
})->with([
    'no posts' => [0],
    'one post' => [1],
    'two posts' => [2],
]);

test('related posts default to three posts', function (): void {
    $current = Post::factory()->create();
    Post::factory()
        ->count(4)
        ->create(['published_at' => now()->subDay()]);

    $related = app(RelatedPostsQuery::class)->get($current);

    expect($related)->toHaveCount(3);
});

test('related posts arrive with their category names loaded', function (): void {
    $category = Category::factory()->create(['name' => 'Sample Topic']);
    $current = Post::factory()
        ->for($category)
        ->create();
    Post::factory()
        ->for($category)
        ->create(['published_at' => now()->subDays(2)]);
    Post::factory()->create(['published_at' => now()->subDays(3)]);

    $related = app(RelatedPostsQuery::class)->get($current);

    expect($related)->toHaveCount(2)
        ->and($related->every(fn (Post $post): bool => $post->relationLoaded('category')))
        ->toBeTrue()
        ->and($related->first()
            ?->category_label)
        ->toBe('Sample Topic');
});

test('related posts of an uncategorized post start with other uncategorized posts', function (): void {
    $current = Post::factory()->create(['category_id' => null]);
    $uncategorized = Post::factory()->create(['category_id' => null, 'published_at' => now()->subDays(3)]);
    $recent = Post::factory()->create(['published_at' => now()->subDay()]);

    $related = app(RelatedPostsQuery::class)->get($current, 2);

    expect($related->modelKeys())->toBe([$uncategorized->id, $recent->id]);
});

test('related posts select only the fields rendered by their cards', function (): void {
    $post = Post::factory()
        ->inCategory('park-accessibility')
        ->create();
    Post::factory()
        ->inCategory('park-accessibility')
        ->create();
    Post::factory()->create(['published_at' => now()->subDays(2)]);

    [$categoryMatch, $recent] = app(RelatedPostsQuery::class)
        ->get($post)
        ->all();

    expect($categoryMatch->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category_id', 'content', 'featured_image_path'])
        ->not->toHaveKeys(['excerpt', 'meta_description', 'category'])
        ->and($recent->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category_id', 'content', 'featured_image_path'])
        ->not->toHaveKeys(['excerpt', 'meta_description', 'category']);
});

test('related posts break publish-time ties by id so their order is stable on MySQL', function (): void {
    $category = Category::factory()->create();
    $post = Post::factory()
        ->for($category)
        ->create();
    Post::factory()
        ->for($category)
        ->count(2)
        ->create();

    $orderings = publishTimeOrderings(fn () => app(RelatedPostsQuery::class)->get($post));

    expect($orderings)->toHaveCount(2)
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
