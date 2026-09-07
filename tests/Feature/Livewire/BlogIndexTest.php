<?php

use App\Livewire\BlogIndex;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('query string filters update the visible stories', function (): void {
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
    ])
        ->test(BlogIndex::class)
        ->assertSeeInOrder([$oldestPost->title, $newestPost->title])
        ->assertViewHas('archivePosts', fn ($posts): bool => ! $posts->contains($unrelatedPost))
        ->assertSet('category', 'park-accessibility')
        ->assertSet('search', 'accessible')
        ->assertSet('sort', 'oldest');
});

test('topic and reset actions preserve valid filter state', function (): void {
    Livewire::withQueryParams([
        'category' => 'not-a-category',
        'q' => str_repeat('a', 120),
        'sort' => 'not-a-sort',
        'page' => 4,
    ])
        ->test(BlogIndex::class)
        ->assertSet('category', '')
        ->assertSet('search', str_repeat('a', 100))
        ->assertSet('sort', 'newest')
        ->call('selectCategory', 'park-accessibility')
        ->assertSet('category', 'park-accessibility')
        ->assertSet('search', '')
        ->assertSet('sort', 'newest')
        ->assertDispatched('blog-metadata-updated')
        ->call('clearFilters')
        ->assertSet('category', '')
        ->assertSet('search', '')
        ->assertSet('sort', 'newest');
});

test('featured story remains visible on subsequent archive pages', function (): void {
    $featuredPost = Post::factory()->create([
        'title' => 'Featured throughout the archive',
        'published_at' => now(),
    ]);
    Post::factory()->count(12)->create([
        'published_at' => now()->subDay(),
    ]);

    Livewire::withQueryParams(['page' => 2])
        ->test(BlogIndex::class)
        ->assertSee($featuredPost->title);
});

test('featured story is independent of category search and sort filters', function (): void {
    $featuredPost = Post::factory()->create(['title' => 'Permanent feature', 'published_at' => now()]);
    Post::factory()->create(['title' => 'Quiet entrance', 'category' => 'park-accessibility', 'published_at' => now()->subWeek()]);
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    Livewire::test(BlogIndex::class)
        ->call('selectCategory', 'park-accessibility')
        ->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost))
        ->set('search', 'no matching stories')
        ->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost))
        ->set('sort', 'oldest')
        ->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost))
        ->call('clearFilters')
        ->assertViewHas('featuredPost', fn (Post $post): bool => $post->is($featuredPost));
});
