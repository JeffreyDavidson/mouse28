<?php

use App\Console\Commands\AttachBundledArtwork;
use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;

covers(AttachBundledArtwork::class);

pest()->use(RefreshDatabase::class);

/**
 * A small real WebP, so the attached artwork can be decoded into responsive variants.
 *
 * @param  positive-int  $width
 * @param  positive-int  $height
 */
function bundledArtworkImage(int $width = 1000, int $height = 500): string
{
    $image = imagecreatetruecolor($width, $height);
    ob_start();
    imagewebp($image);

    return (string) ob_get_clean();
}

test('failed artwork writes leave content unattached and return failure', function (): void {
    // Arrange
    $post = Post::factory()->create(['slug' => 'welcome-to-mouse-28', 'featured_image_path' => null]);
    $disk = Double::for(FilesystemAdapter::class);
    $disk->allows('exists')->returns(false);
    $disk->allows('put')->returns(false);
    Storage::set('public', $disk);

    // Act
    $exitCode = pendingCommand('content:attach-artwork')->run();

    // Assert
    expect($exitCode)->toBe(Command::FAILURE)
        ->and($post->refresh()->featured_image_path)->toBeNull();
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
        File::put("{$sourceDirectory}/{$path}", bundledArtworkImage());
    }
    config()->set('mouse28.content_artwork_path', $sourceDirectory);

    $post = Post::factory()->create([
        'slug' => 'example-post',
        'featured_image_path' => null,
    ]);
    $uploadedPost = Post::factory()->create([
        'slug' => 'example-upload',
        'featured_image_path' => 'posts/custom-upload.webp',
    ]);
    $unmatchedPost = Post::factory()->create([
        'slug' => 'unmatched-post',
        'featured_image_path' => null,
    ]);
    $episode = Episode::factory()->create([
        'slug' => 'example-episode',
        'featured_image_path' => null,
    ]);

    try {
        expect(pendingCommand('content:attach-artwork')->run())->toBe(Command::SUCCESS);

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        foreach ($artwork as $path) {
            $publicDisk->assertExists($path);
        }

        expect($post->refresh()->featured_image_path)->toBe('posts/example-post.webp')
            ->and($uploadedPost->refresh()->featured_image_path)->toBe('posts/custom-upload.webp')
            ->and($unmatchedPost->refresh()->featured_image_path)->toBeNull()
            ->and($episode->refresh()->featured_image_path)->toBe('episodes/example-episode.webp');

        $publicDisk->assertMissing('posts/unmatched-post.webp');
        $publicDisk->assertExists(['posts/responsive/example-post-480.webp', 'episodes/responsive/example-episode-480.webp']);
        $publicDisk->assertMissing(['posts/responsive/example-upload-480.webp', 'posts/responsive/example-post-1280.webp', 'episodes/responsive/example-episode-640.webp']);
        expect(getimagesize($publicDisk->path('episodes/responsive/example-episode-480.webp')))->toMatchArray([0 => 480, 1 => 480])
            ->and(pendingCommand('content:attach-artwork')->run())->toBe(Command::SUCCESS);
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
    File::put("{$sourceDirectory}/posts/a-new-park-story.webp", bundledArtworkImage());
    File::put("{$sourceDirectory}/posts/ignored-cover.jpg", 'unsupported artwork');
    File::put("{$sourceDirectory}/concepts/concept-story.webp", 'concept artwork');
    File::put("{$sourceDirectory}/episodes/a-new-park-story.webp", bundledArtworkImage());
    $episode = Episode::factory()->create(['slug' => 'a-new-park-story', 'featured_image_path' => null]);

    foreach (['example-episode', 'another-example-episode'] as $episodeSlug) {
        File::put("{$sourceDirectory}/episodes/{$episodeSlug}.webp", 'episode artwork');
    }

    config()->set('mouse28.content_artwork_path', $sourceDirectory);

    $post = Post::factory()->create([
        'slug' => 'a-new-park-story',
        'featured_image_path' => null,
    ]);
    $conceptPost = Post::factory()->create([
        'slug' => 'concept-story',
        'featured_image_path' => null,
    ]);

    try {
        expect(pendingCommand('content:attach-bundled-artwork')->run())->toBe(Command::SUCCESS);

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        $publicDisk->assertExists('posts/a-new-park-story.webp');
        $publicDisk->assertMissing('posts/ignored-cover.jpg');
        $publicDisk->assertMissing('concepts/concept-story.webp');

        expect($post->refresh()->featured_image_path)->toBe('posts/a-new-park-story.webp')
            ->and($episode->refresh()->featured_image_path)->toBe('episodes/a-new-park-story.webp')
            ->and($conceptPost->refresh()->featured_image_path)->toBeNull();
    } finally {
        File::deleteDirectory($sourceDirectory);
    }
});

test('attached artwork that cannot be decoded stays attached and the command reports a failure', function (): void {
    Storage::fake('public');
    $sourceDirectory = storage_path('framework/testing/undecodable-content-artwork');
    File::deleteDirectory($sourceDirectory);
    File::ensureDirectoryExists("{$sourceDirectory}/posts");
    File::ensureDirectoryExists("{$sourceDirectory}/episodes");
    File::put("{$sourceDirectory}/posts/broken-story.webp", 'not an image');
    config()->set('mouse28.content_artwork_path', $sourceDirectory);
    $post = Post::factory()->create(['slug' => 'broken-story']);

    try {
        pendingCommand('content:attach-bundled-artwork')
            ->expectsOutputToContain('Responsive images could not be generated for 1 attached artwork files.')
            ->assertFailed();

        expect($post->refresh()->featured_image_path)->toBe('posts/broken-story.webp');
    } finally {
        File::deleteDirectory($sourceDirectory);
    }
});
