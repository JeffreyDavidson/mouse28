<?php

use App\Console\Commands\GeneratePostImageVariants;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

covers(GeneratePostImageVariants::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

test('it backfills variants, skips verified posts and regenerates them with force', function (): void {
    Storage::disk('public')->put('posts/post.png', UploadedFile::fake()
        ->image('post.png', 1280, 72)
        ->getContent());
    Post::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'posts/post.png'])
        ->saveQuietly();

    pendingCommand('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists(['posts/responsive/post-480.webp', 'posts/responsive/post-1280.webp']);

    pendingCommand('posts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 posts.')
        ->expectsOutputToContain('Skipped 1 already verified post.')
        ->assertSuccessful();

    pendingCommand('posts:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->doesntExpectOutputToContain('Skipped')
        ->assertSuccessful();
});

test('it fails without exposing the path when a post image is missing', function (): void {
    $post = Post::factory()->create();
    $post->forceFill(['featured_image_path' => 'posts/private-name.png'])
        ->saveQuietly();

    pendingCommand('posts:generate-image-variants')
        ->expectsOutputToContain("Skipped post {$post->id}: its source image is missing or unsupported.")
        ->doesntExpectOutputToContain('private-name')
        ->assertFailed();
});

test('it refuses to overlap another post image generation run', function (): void {
    $lock = Cache::lock('framework/command-posts:generate-image-variants', 60);
    expect($lock->get())->toBeTrue();

    try {
        pendingCommand('posts:generate-image-variants')
            ->expectsOutputToContain('The [posts:generate-image-variants] command is already running.')
            ->assertFailed();
    } finally {
        $lock->release();
    }
});
