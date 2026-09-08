<?php

use App\Livewire\BlogIndex;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

test('query string filters update the visible stories', function (): void {
    // Arrange
    $newestPost = Post::factory()->create([
        'title' => 'Newest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subDay(),
    ]);
    $oldestPost = Post::factory()->create([
        'title' => 'Oldest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subWeek(),
    ]);
    $unrelatedPost = Post::factory()->create([
        'title' => 'Unrelated dining review',
        'category' => 'food-reviews',
    ]);

    Livewire::withQueryParams([
        'category' => 'park-accessibility',
        'q' => 'accessible',
        'sort' => 'oldest',
    ]);

    // Act
    $page = Livewire::test(BlogIndex::class);

    // Assert
    $page->assertSeeInOrder([$oldestPost->title, $newestPost->title])
        ->assertViewHas('archivePosts', fn ($posts): bool => ! $posts->contains($unrelatedPost));
    $page->assertSet('category', 'park-accessibility')
        ->assertSet('search', 'accessible')
        ->assertSet('sort', 'oldest')
        ->assertViewHas('hasAnyPosts', true);

    // Act
    $page->set('search', 'no matching story');

    // Assert
    $page->assertViewHas('archivePosts', fn ($posts): bool => $posts->isEmpty())
        ->assertViewHas('hasAnyPosts', true);
});

test('topic and reset actions preserve valid filter state', function (): void {
    // Arrange
    Livewire::withQueryParams([
        'category' => 'not-a-category',
        'q' => str_repeat('a', 120),
        'sort' => 'not-a-sort',
        'page' => 4,
    ]);

    // Act
    $page = Livewire::test(BlogIndex::class);

    // Assert
    $page->assertSet('category', '')
        ->assertSet('search', str_repeat('a', 100))
        ->assertSet('sort', 'newest');

    // Act
    $page->call('selectCategory', 'park-accessibility');

    // Assert
    $page->assertSet('category', 'park-accessibility')
        ->assertSet('search', '')
        ->assertSet('sort', 'newest')
        ->assertDispatched('blog-metadata-updated');

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
    $featuredPost = Post::factory()->create([
        'title' => 'Featured throughout the archive',
        'published_at' => now(),
    ]);
    Post::factory()->count(12)->create([
        'published_at' => now()->subDay(),
    ]);

    Livewire::withQueryParams(['page' => 2]);

    // Act
    $page = Livewire::test(BlogIndex::class);

    // Assert
    $page->assertSee($featuredPost->title);
});

test('featured story is independent of category search and sort filters', function (): void {
    // Arrange
    $featuredPost = Post::factory()->create(['title' => 'Permanent feature', 'published_at' => now()]);
    Post::factory()->create(['title' => 'Quiet entrance', 'category' => 'park-accessibility', 'published_at' => now()->subWeek()]);
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    $page = Livewire::test(BlogIndex::class);

    // Act
    $page->call('selectCategory', 'park-accessibility');

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
