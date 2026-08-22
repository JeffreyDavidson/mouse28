<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('bundled artwork is attached without replacing existing uploads', function (): void {
    Storage::fake('public');

    $artwork = [
        'posts/our-disney-park-bag-essentials.webp',
        'posts/disney-dining-with-a-picky-eater.webp',
        'posts/the-ride-that-surprised-us.webp',
        'posts/what-is-das-and-how-it-changed-our-disney-visits.webp',
        'posts/a-first-timers-guide-to-disney-world-with-a-sensory-sensitive-child.webp',
        'episodes/trailer-meet-mouse28.webp',
        'episodes/meet-jeffrey-and-cassie-our-disney-story.webp',
    ];

    $post = Post::factory()->create([
        'slug' => 'our-disney-park-bag-essentials',
        'cover_image' => null,
    ]);
    $episode = Episode::factory()->create([
        'slug' => 'trailer-meet-mouse28',
        'cover_image' => 'episodes/custom-upload.webp',
    ]);

    expect($this->artisan('content:attach-artwork'))->toBe(Command::SUCCESS);

    foreach ($artwork as $path) {
        Storage::disk('public')->assertExists($path);
    }

    expect($post->refresh()->cover_image)->toBe('posts/our-disney-park-bag-essentials.webp')
        ->and($episode->refresh()->cover_image)->toBe('episodes/custom-upload.webp');

    expect($this->artisan('content:attach-artwork'))->toBe(Command::SUCCESS);
});

test('artwork attachment stops when a bundled file is missing', function (): void {
    Storage::fake('public');
    config()->set('mouse28.content_artwork_path', storage_path('framework/testing/missing-artwork'));

    expect($this->artisan('content:attach-artwork'))->toBe(Command::FAILURE);
});
