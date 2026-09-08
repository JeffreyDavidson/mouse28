<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\ViewModels\HomeViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('homepage content queries select only fields rendered by their cards', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create(['title' => 'Featured sensory planning post']);
    Post::factory()->create(['title' => 'Latest sensory planning post']);
    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Episode::factory()->create(['title' => 'Sensory planning episode']);

    $data = app(HomeViewModel::class)->data();

    expect($data['featuredPost']->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category', 'cover_image'])
        ->not->toHaveKeys(['body', 'excerpt', 'meta_description'])
        ->and($data['latestPosts']->first()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category', 'cover_image', 'published_at'])
        ->not->toHaveKeys(['body', 'excerpt', 'meta_description'])
        ->and($data['latestGuides']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'excerpt', 'category', 'cover_image'])
        ->not->toHaveKeys(['body', 'meta_description'])
        ->and($data['latestEpisodes']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'description', 'episode_number', 'duration_seconds'])
        ->not->toHaveKeys(['show_notes', 'transcript', 'meta_description']);
});

test('homepage planning posts select only their rendered fields', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create([
        'title' => 'Sensory planning post',
        'category' => 'park-accessibility',
    ]);

    $data = app(HomeViewModel::class)->data();

    expect($data['planningPosts']->sole()->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category', 'cover_image'])
        ->not->toHaveKeys(['body', 'excerpt', 'meta_description']);
});
