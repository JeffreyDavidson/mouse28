<?php

use App\Console\Commands\RepairResponsiveImages;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

covers(RepairResponsiveImages::class);

pest()->use(RefreshDatabase::class);

test('it repairs variants for posts, episodes, guides and the podcast, then verifies them', function (): void {
    $wide = UploadedFile::fake()
        ->image('wide.png', 1000, 520)
        ->getContent();
    Storage::disk('public')->put('posts/post.png', $wide);
    Storage::disk('public')->put('posts/responsive/post-1280.webp', 'obsolete');
    Storage::disk('public')->put('episodes/episode.png', $wide);
    Storage::disk('public')->put('guides/guide.png', $wide);
    Storage::disk('public')->put('podcast/cover.png', $wide);
    Post::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'posts/post.png'])
        ->saveQuietly();
    Episode::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'episodes/episode.png'])
        ->saveQuietly();
    Guide::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'guides/guide.png'])
        ->saveQuietly();
    primaryPodcast()->forceFill(['cover_image_path' => 'podcast/cover.png'])
        ->saveQuietly();
    app(ResponsiveImageVariants::class)->generate('podcast/cover.png');

    pendingCommand('media:repair-responsive-images')
        ->expectsOutputToContain('Generated responsive images for 1 post.')
        ->expectsOutputToContain('Generated responsive images for 1 episode.')
        ->expectsOutputToContain('Generated responsive images for 1 guide.')
        ->expectsOutputToContain('Generated responsive images for 0 podcasts.')
        ->expectsOutputToContain('Skipped 1 already verified podcast.')
        ->expectsOutputToContain('Episode: 1 checked, 1 verified, 0 failed.')
        ->expectsOutputToContain('Responsive image verification passed.')
        ->expectsOutputToContain('Responsive image repair completed successfully.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists(['posts/responsive/post-768.webp', 'guides/responsive/guide-768.webp', 'episodes/responsive/episode-480.webp']);
    Storage::disk('public')->assertMissing(['posts/responsive/post-1280.webp', 'episodes/responsive/episode-640.webp']);
    expect(getimagesize(Storage::disk('public')->path('episodes/responsive/episode-480.webp')))->toMatchArray([0 => 480, 1 => 480]);

    pendingCommand('media:repair-responsive-images', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 podcast.')
        ->doesntExpectOutputToContain('already verified')
        ->assertSuccessful();
});

test('it repairs the remaining media before reporting a failure', function (): void {
    Storage::disk('public')->put('posts/broken.png', 'not an image');
    Storage::disk('public')->put('guides/guide.png', UploadedFile::fake()
        ->image('guide.png', 800, 45)
        ->getContent());
    Post::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'posts/broken.png'])
        ->saveQuietly();
    Guide::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'guides/guide.png'])
        ->saveQuietly();

    pendingCommand('media:repair-responsive-images')
        ->expectsOutputToContain('Generated responsive images for 1 guide.')
        ->expectsOutputToContain('Post: 1 checked, 0 verified, 1 failed.')
        ->expectsOutputToContain('Responsive image verification failed.')
        ->expectsOutputToContain('Responsive image repair completed with failures.')
        ->assertFailed();

    Storage::disk('public')->assertExists('guides/responsive/guide-480.webp');
});

test('it refuses to overlap another responsive image repair', function (): void {
    $lock = Cache::lock('framework/command-media:repair-responsive-images', 60);
    expect($lock->get())->toBeTrue();

    try {
        pendingCommand('media:repair-responsive-images')
            ->expectsOutputToContain('The [media:repair-responsive-images] command is already running.')
            ->assertFailed();
    } finally {
        $lock->release();
    }
});
