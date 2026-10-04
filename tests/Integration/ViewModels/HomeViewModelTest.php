<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\ViewModels\HomeViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(HomeViewModel::class);

pest()->use(RefreshDatabase::class);

test('homepage content queries select only fields rendered by their cards', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create(['title' => 'Featured sensory planning post']);
    Post::factory()->create(['title' => 'Latest sensory planning post']);
    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Episode::factory()->create(['title' => 'Sensory planning episode']);

    $data = app(HomeViewModel::class)->data();

    expect($data['featuredPost']?->getAttributes() ?? [])
        ->toHaveKeys(['id', 'slug', 'title', 'category_id', 'featured_image_path'])
        ->not->toHaveKeys(['content', 'excerpt', 'meta_description', 'category'])
        ->and($data['latestPosts']->firstOrFail()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category_id', 'featured_image_path', 'published_at'])
        ->not->toHaveKeys(['content', 'excerpt', 'meta_description', 'category'])
        ->and($data['latestGuides']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'excerpt', 'category', 'featured_image_path'])
        ->not->toHaveKeys(['content', 'meta_description'])
        ->and($data['latestEpisodes']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'description', 'episode_number', 'duration_seconds'])
        ->not->toHaveKeys(['show_notes', 'transcript', 'meta_description']);
});

test('homepage planning posts select only their rendered fields', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->inCategory('park-accessibility')->create([
        'title' => 'Sensory planning post',
    ]);

    $data = app(HomeViewModel::class)->data();

    expect($data['planningPosts']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category_id', 'featured_image_path'])
        ->not->toHaveKeys(['content', 'excerpt', 'meta_description', 'category']);
});

test('homepage posts arrive with their category names loaded', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $category = Category::factory()->create(['name' => 'Sample Topic']);
    Post::factory()->for($category)->count(2)->create();
    Post::factory()->inCategory('disney-tips')->create(['published_at' => now()->subWeek()]);

    $data = app(HomeViewModel::class)->data();

    expect($data['featuredPost']?->relationLoaded('category'))->toBeTrue()
        ->and($data['featuredPost']?->category_label)->toBe('Sample Topic')
        ->and($data['latestPosts']->every(fn (Post $post): bool => $post->relationLoaded('category')))->toBeTrue()
        ->and($data['planningPosts']->sole()->relationLoaded('category'))->toBeTrue()
        ->and($data['planningPosts']->sole()->category_label)->toBe('Disney Tips');
});

test('homepage planning posts come only from the three planning categories', function (string $slug): void {
    config()->set('mouse28.guides_enabled', true);
    $planningPost = Post::factory()->inCategory($slug)->create(['published_at' => now()->subDays(2)]);
    Post::factory()->inCategory('general')->create(['published_at' => now()->subDay()]);
    Post::factory()->create(['published_at' => now()->subHour()]);
    Post::factory()->create(['category_id' => null]);

    $data = app(HomeViewModel::class)->data();

    expect($data['planningPosts']->modelKeys())->toBe([$planningPost->id]);
})->with(['park-accessibility', 'disney-tips', 'autism-awareness']);

test('homepage planning posts are the two newest published planning stories', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $newest = Post::factory()->inCategory('autism-awareness')->create(['published_at' => now()->subDay()]);
    $second = Post::factory()->inCategory('park-accessibility')->create(['published_at' => now()->subDays(2)]);
    Post::factory()->inCategory('disney-tips')->create(['published_at' => now()->subDays(3)]);
    Post::factory()->inCategory('disney-tips')->draft()->create();

    $data = app(HomeViewModel::class)->data();

    expect($data['planningPosts']->modelKeys())->toBe([$newest->id, $second->id]);
});
