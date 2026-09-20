<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;

pest()->use(RefreshDatabase::class);

test('failed artwork writes leave content unattached and return failure', function (): void {
    // Arrange
    $post = Post::factory()->create(['slug' => 'welcome-to-mouse-28', 'cover_image' => null]);
    $disk = Double::for(FilesystemAdapter::class);
    $disk->allows('exists')->returns(false);
    $disk->allows('put')->returns(false);
    Storage::set('public', $disk);

    // Act
    $exitCode = pendingCommand('content:attach-artwork')->run();

    // Assert
    expect($exitCode)->toBe(Command::FAILURE)
        ->and($post->refresh()->cover_image)->toBeNull();
});

test('bundled artwork attaches matching records, preserves uploads, and is idempotent', function (): void {
    Storage::fake('public');

    $sourceDirectory = storage_path('framework/testing/bundled-content-artwork');
    File::deleteDirectory($sourceDirectory);
    File::ensureDirectoryExists("{$sourceDirectory}/posts");
    File::ensureDirectoryExists("{$sourceDirectory}/episodes");

    $artwork = [
        'posts/example-post.webp',
        'posts/example-upload.webp',
        'episodes/example-episode.webp',
    ];
    foreach ($artwork as $path) {
        File::put("{$sourceDirectory}/{$path}", 'dummy artwork');
    }
    config()->set('mouse28.content_artwork_path', $sourceDirectory);

    $post = Post::factory()->create([
        'slug' => 'example-post',
        'cover_image' => null,
    ]);
    $uploadedPost = Post::factory()->create([
        'slug' => 'example-upload',
        'cover_image' => 'posts/custom-upload.webp',
    ]);
    $unmatchedPost = Post::factory()->create([
        'slug' => 'unmatched-post',
        'cover_image' => null,
    ]);
    $episode = Episode::factory()->create([
        'slug' => 'example-episode',
        'cover_image' => null,
    ]);

    try {
        expect(pendingCommand('content:attach-artwork')->run())->toBe(Command::SUCCESS);

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        foreach ($artwork as $path) {
            $publicDisk->assertExists($path);
        }

        expect($post->refresh()->cover_image)->toBe('posts/example-post.webp')
            ->and($uploadedPost->refresh()->cover_image)->toBe('posts/custom-upload.webp')
            ->and($unmatchedPost->refresh()->cover_image)->toBeNull()
            ->and($episode->refresh()->cover_image)->toBe('episodes/example-episode.webp');

        $publicDisk->assertMissing('posts/unmatched-post.webp');

        expect(pendingCommand('content:attach-artwork')->run())->toBe(Command::SUCCESS);
    } finally {
        File::deleteDirectory($sourceDirectory);
    }
});

test('artwork attachment stops when a bundled file is missing', function (): void {
    Storage::fake('public');
    config()->set('mouse28.content_artwork_path', storage_path('framework/testing/missing-artwork'));

    pendingCommand('content:attach-artwork')
        ->expectsOutputToContain('Bundled artwork directory is missing:')
        ->assertFailed();
});

test('bundled artwork ignores unsupported files and inactive concepts', function (): void {
    Storage::fake('public');

    $sourceDirectory = storage_path('framework/testing/discovered-content-artwork');
    File::deleteDirectory($sourceDirectory);
    File::ensureDirectoryExists("{$sourceDirectory}/posts");
    File::ensureDirectoryExists("{$sourceDirectory}/episodes");
    File::ensureDirectoryExists("{$sourceDirectory}/concepts");
    File::put("{$sourceDirectory}/posts/a-new-park-story.webp", 'post artwork');
    File::put("{$sourceDirectory}/posts/ignored-cover.jpg", 'unsupported artwork');
    File::put("{$sourceDirectory}/concepts/concept-story.webp", 'concept artwork');
    File::put("{$sourceDirectory}/episodes/a-new-park-story.webp", 'episode artwork');
    $episode = Episode::factory()->create(['slug' => 'a-new-park-story', 'cover_image' => null]);

    foreach (['example-episode', 'another-example-episode'] as $episodeSlug) {
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
        expect(pendingCommand('content:attach-bundled-artwork')->run())->toBe(Command::SUCCESS);

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        $publicDisk->assertExists('posts/a-new-park-story.webp');
        $publicDisk->assertMissing('posts/ignored-cover.jpg');
        $publicDisk->assertMissing('concepts/concept-story.webp');

        expect($post->refresh()->cover_image)->toBe('posts/a-new-park-story.webp')
            ->and($episode->refresh()->cover_image)->toBe('episodes/a-new-park-story.webp')
            ->and($conceptPost->refresh()->cover_image)->toBeNull();
    } finally {
        File::deleteDirectory($sourceDirectory);
    }
});
