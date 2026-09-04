<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('bundled artwork is attached without replacing existing uploads', function (): void {
    Storage::fake('public');

    $artwork = [
        'posts/welcome-to-mouse-28.webp',
        'posts/our-disney-park-bag-essentials.webp',
        'posts/disney-dining-with-a-picky-eater.webp',
        'posts/the-ride-that-surprised-us.webp',
        'posts/what-is-das-and-how-it-changed-our-disney-visits.webp',
        'posts/a-first-timers-guide-to-disney-world-with-a-sensory-sensitive-child.webp',
        'episodes/trailer-meet-mouse28.webp',
        'episodes/meet-jeffrey-and-cassie-our-disney-story.webp',
    ];

    $welcomePost = Post::factory()->create([
        'slug' => 'welcome-to-mouse-28',
        'cover_image' => null,
    ]);
    $post = Post::factory()->create([
        'slug' => 'our-disney-park-bag-essentials',
        'cover_image' => null,
    ]);
    $uploadedPost = Post::factory()->create([
        'slug' => 'disney-dining-with-a-picky-eater',
        'cover_image' => 'posts/custom-upload.webp',
    ]);
    $conceptPost = Post::factory()->create([
        'slug' => 'recap-epcot-kids-think-differently-ep4',
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

    expect($welcomePost->refresh()->cover_image)->toBe('posts/welcome-to-mouse-28.webp')
        ->and($post->refresh()->cover_image)->toBe('posts/our-disney-park-bag-essentials.webp')
        ->and($uploadedPost->refresh()->cover_image)->toBe('posts/custom-upload.webp')
        ->and($conceptPost->refresh()->cover_image)->toBeNull()
        ->and($episode->refresh()->cover_image)->toBe('episodes/custom-upload.webp');

    Storage::disk('public')->assertMissing('posts/recap-epcot-kids-think-differently-ep4.webp');

    expect($this->artisan('content:attach-artwork'))->toBe(Command::SUCCESS);
});

test('artwork attachment stops when a bundled file is missing', function (): void {
    Storage::fake('public');
    config()->set('mouse28.content_artwork_path', storage_path('framework/testing/missing-artwork'));

    expect($this->artisan('content:attach-artwork'))->toBe(Command::FAILURE);
});

test('new post artwork is discovered by its slug while concepts stay inactive', function (): void {
    Storage::fake('public');

    $sourceDirectory = storage_path('framework/testing/discovered-content-artwork');
    File::deleteDirectory($sourceDirectory);
    File::ensureDirectoryExists("{$sourceDirectory}/posts");
    File::ensureDirectoryExists("{$sourceDirectory}/episodes");
    File::ensureDirectoryExists("{$sourceDirectory}/concepts");
    File::put("{$sourceDirectory}/posts/a-new-park-story.webp", 'post artwork');
    File::put("{$sourceDirectory}/posts/ignored-cover.jpg", 'unsupported artwork');
    File::put("{$sourceDirectory}/concepts/concept-story.webp", 'concept artwork');

    foreach (['trailer-meet-mouse28', 'meet-jeffrey-and-cassie-our-disney-story'] as $episodeSlug) {
        File::put("{$sourceDirectory}/episodes/{$episodeSlug}.webp", 'episode artwork');
    }

    config()->set('mouse28.content_artwork_path', $sourceDirectory);

    $post = Post::factory()->create([
        'slug' => 'a-new-park-story',
        'cover_image' => null,
    ]);
    $conceptPost = Post::factory()->create([
        'slug' => 'concept-story',
        'cover_image' => null,
    ]);

    try {
        expect($this->artisan('content:attach-artwork'))->toBe(Command::SUCCESS);

        Storage::disk('public')->assertExists('posts/a-new-park-story.webp');
        Storage::disk('public')->assertMissing('posts/ignored-cover.jpg');
        Storage::disk('public')->assertMissing('concepts/concept-story.webp');

        expect($post->refresh()->cover_image)->toBe('posts/a-new-park-story.webp')
            ->and($conceptPost->refresh()->cover_image)->toBeNull();
    } finally {
        File::deleteDirectory($sourceDirectory);
    }
});
