<?php

use App\Livewire\BlogArchive;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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
    $accessibility = Category::factory()->create();
    $dining = Category::factory()->create();
    $newestPost = Post::factory()->for($accessibility)->create([
        'title' => 'Newest accessible plan',
        'published_at' => now()->subDay(),
    ]);
    $oldestPost = Post::factory()->for($accessibility)->create([
        'title' => 'Oldest accessible plan',
        'published_at' => now()->subWeek(),
    ]);
    $unrelatedPost = Post::factory()->for($dining)->create([
        'title' => 'Unrelated dining review',
    ]);

    Livewire::withQueryParams([
        'category' => $accessibility->slug,
        'q' => 'accessible',
        'sort' => 'oldest',
    ]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertSeeInOrder([$oldestPost->title, $newestPost->title])
        ->assertViewHas('archivePosts', fn (Collection $posts): bool => $posts->doesntContain($unrelatedPost));
    $page->assertSet('category', $accessibility->slug)
        ->assertSet('search', 'accessible')
        ->assertSet('sort', 'oldest')
        ->assertViewHas('hasAnyPosts', true)
        ->assertViewHas('usedCategories', fn (Collection $categories): bool => $categories->pluck('slug')->all() === [
            $accessibility->slug,
            $dining->slug,
        ]);

    // Act
    $page->set('search', 'no matching story');

    // Assert
    $page->assertViewHas('archivePosts', fn (Collection $posts): bool => $posts->isEmpty())
        ->assertViewHas('hasAnyPosts', true);
});

test('query string filters offer only categories with published stories, labelled by name', function (): void {
    // Arrange
    $used = Category::factory()->create(['name' => 'Sample Used Topic']);
    $draftOnly = Category::factory()->create(['name' => 'Sample Draft Topic']);
    $scheduledOnly = Category::factory()->create(['name' => 'Sample Scheduled Topic']);
    Category::factory()->create(['name' => 'Sample Empty Topic']);
    Post::factory()->for($used)->create();
    Post::factory()->for($draftOnly)->draft()->create();
    Post::factory()->for($scheduledOnly)->scheduled()->create();
    Post::factory()->create(['category_id' => null]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertViewHas('usedCategories', fn (Collection $categories): bool => $categories->pluck('slug')->all() === [$used->slug])
        ->assertSeeHtml('href="'.e(route('blog.index', ['category' => $used->slug])).'"')
        ->assertSee('Sample Used Topic')
        ->assertDontSee('Sample Draft Topic')
        ->assertDontSee('Sample Scheduled Topic')
        ->assertDontSee('Sample Empty Topic');
});

test('query string filters show the selected category name as the heading', function (): void {
    // Arrange
    $category = Category::factory()->create(['name' => 'Sample Heading Topic']);
    Post::factory()->for($category)->create();
    Livewire::withQueryParams(['category' => $category->slug]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertSeeHtml('Sample Heading Topic')
        ->assertDontSee('More stories');
});

test('query string filters name an existing category that has no stories yet', function (): void {
    // Arrange
    $category = Category::factory()->create(['name' => 'Sample Quiet Topic']);
    Post::factory()->create();
    Livewire::withQueryParams(['category' => $category->slug]);

    // Act
    $page = livewire(BlogArchive::class);

    // Assert
    $page->assertSet('category', $category->slug)
        ->assertSee('Nothing in Sample Quiet Topic yet');
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
    $category = Category::factory()->create();
    Livewire::withQueryParams([
        'category' => 'not-a-category',
        'q' => str_repeat('a', 120),
        'sort' => 'not-a-sort',
        'page' => 4,
    ]);

    // Act
    $page = livewire(BlogArchive::class);
    $page->call('selectCategory', $category->slug);

    // Assert
    $page->assertSet('category', $category->slug)
        ->assertSet('search', '')
        ->assertSet('sort', 'newest')
        ->assertDispatched('blog-metadata-updated');
});

test('clearing filters restores the default archive state', function (): void {
    // Arrange
    Livewire::withQueryParams([
        'category' => Category::factory()->create()->slug,
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
    $category = Category::factory()->create();
    Post::factory()->for($category)->create();
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    $page = livewire(BlogArchive::class);

    // Act
    $page->call('selectCategory', $category->slug);

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
    Post::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'content' => '']);
    Post::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'content' => '']);

    $page = livewire(BlogArchive::class, ['search' => $search]);

    $page->assertViewHas('posts', fn (LengthAwarePaginator $posts): bool => $posts->total() === $expectedCount);
})->with([
    'percent sign' => ['100%', 1],
    'underscore' => ['_', 0],
]);

test('archive search matches text in the post content', function (): void {
    $matching = Post::factory()->create(['title' => 'Arrival tips', 'excerpt' => '', 'content' => 'Find the quietzone near the entrance.']);
    $unrelated = Post::factory()->create(['title' => 'Unrelated', 'excerpt' => '', 'content' => 'Nothing to see here.']);

    $page = livewire(BlogArchive::class, ['search' => 'quietzone']);

    $page->assertViewHas('archivePosts', fn (Collection $posts): bool => $posts->contains($matching) && $posts->doesntContain($unrelated));
});

test('selecting a category that does not exist shows every story', function (): void {
    // Arrange
    $posts = Post::factory()->count(2)->create();
    Livewire::withQueryParams(['category' => $posts->first()?->category?->slug]);
    $page = livewire(BlogArchive::class);

    // Act
    $page->call('selectCategory', 'not-a-category');

    // Assert
    $page->assertSet('category', '')
        ->assertViewHas('posts', fn (LengthAwarePaginator $paginator): bool => $paginator->total() === 2);
});

test('query string filters without a category look up no category by slug', function (): void {
    // Arrange
    Post::factory()->create();
    $emptySlugLookups = 0;
    DB::listen(function (QueryExecuted $query) use (&$emptySlugLookups): void {
        if (in_array('', $query->bindings, true)) {
            $emptySlugLookups++;
        }
    });

    // Act
    $page = livewire(BlogArchive::class);
    $page->call('selectCategory', '');

    // Assert
    $page->assertSet('category', '');
    expect($emptySlugLookups)->toBe(0);
});
