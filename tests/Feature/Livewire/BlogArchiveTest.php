<?php

use App\Enums\PostCategory;
use App\Livewire\BlogArchive;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

covers(BlogArchive::class);

test('equal publication dates have stable ordering across archive pages', function (): void {
    // Arrange
    $pageSize = 2;
    config()->set('mouse28.blog_posts_per_page', $pageSize);
    $records = Post::factory()->count($pageSize + 1)->create(['published_at' => now()->subDay()]);
    $ids = $records->modelKeys();
    rsort($ids);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->getCollection()->pluck('id')->all() === array_slice($ids, 0, $pageSize));

    // Act
    $page->call('setPage', 2);

    // Assert
    $page->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->getCollection()->pluck('id')->all() === array_slice($ids, $pageSize, $pageSize));

    // Act
    $page->set('sort', 'oldest');
    sort($ids);

    // Assert
    $page->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->getCollection()->pluck('id')->all() === array_slice($ids, 0, $pageSize));
});

test('query string filters update the visible stories', function (): void {
    // Arrange
    $newestPost = Post::factory()->create([
        'title' => 'Newest accessible plan',
        'category' => PostCategory::ParkAccessibility,
        'published_at' => now()->subDay(),
    ]);
    $oldestPost = Post::factory()->create([
        'title' => 'Oldest accessible plan',
        'category' => PostCategory::ParkAccessibility,
        'published_at' => now()->subWeek(),
    ]);
    $unrelatedPost = Post::factory()->create([
        'title' => 'Unrelated dining review',
        'category' => PostCategory::FoodReviews,
    ]);

    Livewire::withQueryParams([
        'category' => PostCategory::ParkAccessibility->value,
        'q' => 'accessible',
        'sort' => 'oldest',
    ]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertSeeInOrder([$oldestPost->title, $newestPost->title])
        ->assertViewHas('archivePosts', fn (Collection $posts): bool => $posts->doesntContain($unrelatedPost));
    $page->assertSet('category', PostCategory::ParkAccessibility->value)
        ->assertSet('search', 'accessible')
        ->assertSet('sort', 'oldest')
        ->assertViewHas('hasAnyPosts', true)
        ->assertViewHas('usedCategories', function (array $categories): bool {
            sort($categories);

            return $categories === [
                PostCategory::FoodReviews->value,
                PostCategory::ParkAccessibility->value,
            ];
        });

    // Act
    $page->set('search', 'no matching story');

    // Assert
    $page->assertViewHas('archivePosts', fn (Collection $posts): bool => $posts->isEmpty())
        ->assertViewHas('hasAnyPosts', true);
});

test('invalid query filters are normalized on mount', function (): void {
    // Arrange
    Livewire::withQueryParams([
        'category' => 'not-a-category',
        'q' => str_repeat('a', 120),
        'sort' => 'not-a-sort',
        'page' => 4,
    ]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertSet('category', '')
        ->assertSet('search', str_repeat('a', 100))
        ->assertSet('sort', 'newest');
});

test('selecting a category resets incompatible filters', function (): void {
    // Arrange
    Livewire::withQueryParams([
        'category' => 'not-a-category',
        'q' => str_repeat('a', 120),
        'sort' => 'not-a-sort',
        'page' => 4,
    ]);

    // Act
    $page = livewire(BlogArchive::class);
    $page->call('selectCategory', PostCategory::ParkAccessibility->value);

    // Assert
    $page->assertSet('category', PostCategory::ParkAccessibility->value)
        ->assertSet('search', '')
        ->assertSet('sort', 'newest')
        ->assertDispatched('blog-metadata-updated');
});

test('clearing filters restores the default archive state', function (): void {
    // Arrange
    Livewire::withQueryParams([
        'category' => PostCategory::ParkAccessibility->value,
        'q' => 'accessible',
        'sort' => 'oldest',
    ]);
    $page = livewire(BlogArchive::class);

    // Act
    $page->call('clearFilters');

    // Assert
    $page->assertSet('category', '')
        ->assertSet('search', '')
        ->assertSet('sort', 'newest')
        ->assertViewHas('hasAnyPosts', false);
});

test('featured story remains visible on subsequent archive pages', function (): void {
    // Arrange
    config()->set('mouse28.blog_posts_per_page', 2);
    $featuredPost = Post::factory()->create(['published_at' => now()]);
    Post::factory()->count(2)->create([
        'published_at' => now()->subDay(),
    ]);

    Livewire::withQueryParams(['page' => 2]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));
});

test('featured story is independent of category search and sort filters', function (): void {
    // Arrange
    $featuredPost = Post::factory()->create(['published_at' => now()]);
    Post::factory()->create([
        'category' => PostCategory::ParkAccessibility,
    ]);
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    $page = livewire(BlogArchive::class);

    // Act
    $page->call('selectCategory', PostCategory::ParkAccessibility->value);

    // Assert
    $page->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));

    // Act
    $page->set('search', 'no matching stories');

    // Assert
    $page->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));

    // Act
    $page->set('sort', 'oldest');

    // Assert
    $page->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));

    // Act
    $page->call('clearFilters');

    // Assert
    $page->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));
});

test('archive search treats wildcard characters as literal text', function (string $search, int $expectedCount): void {
    Post::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'body' => '']);
    Post::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'body' => '']);

    $page = livewire(BlogArchive::class, ['search' => $search]);

    $page->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->total() === $expectedCount);
})->with([
    'percent sign' => ['100%', 1],
    'underscore' => ['_', 0],
]);
