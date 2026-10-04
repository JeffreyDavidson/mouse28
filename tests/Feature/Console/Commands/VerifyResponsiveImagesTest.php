<?php

use App\Console\Commands\VerifyResponsiveImages;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use App\Services\SquareResponsiveImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(VerifyResponsiveImages::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

test('it reports aggregate results for every media type', function (): void {
    $image = UploadedFile::fake()->image('image.png', 800, 500)->getContent();
    Storage::disk('public')->put('posts/post.png', $image);
    Storage::disk('public')->put('episodes/episode.png', $image);
    Post::factory()->create()->forceFill(['featured_image_path' => 'posts/post.png'])->saveQuietly();
    Episode::factory()->create()->forceFill(['featured_image_path' => 'episodes/episode.png'])->saveQuietly();
    app(ResponsiveImageVariants::class)->generate('posts/post.png');
    app(SquareResponsiveImageVariants::class)->generate('episodes/episode.png');

    pendingCommand('media:verify-responsive-images')
        ->expectsOutputToContain('Posts: 1 checked, 1 verified, 0 failed.')
        ->expectsOutputToContain('Episodes: 1 checked, 1 verified, 0 failed.')
        ->expectsOutputToContain('Guides: 0 checked, 0 verified, 0 failed.')
        ->expectsOutputToContain('Podcasts: 0 checked, 0 verified, 0 failed.')
        ->expectsOutputToContain('Responsive image verification passed.')
        ->assertSuccessful();
});

test('it fails without exposing media paths when variants are missing', function (): void {
    Storage::disk('public')->put('guides/private-guide-name.png', UploadedFile::fake()->image('guide.png', 800, 45)->getContent());
    Guide::factory()->create()->forceFill(['featured_image_path' => 'guides/private-guide-name.png'])->saveQuietly();

    pendingCommand('media:verify-responsive-images')
        ->expectsOutputToContain('Guides: 1 checked, 0 verified, 1 failed.')
        ->doesntExpectOutputToContain('private-guide-name')
        ->expectsOutputToContain('Responsive image verification failed.')
        ->assertFailed();
});

test('it rejects wide episode variants that are not square crops of the original', function (): void {
    Storage::disk('public')->put('episodes/episode.png', UploadedFile::fake()->image('episode.png', 800, 500)->getContent());
    Episode::factory()->create()->forceFill(['featured_image_path' => 'episodes/episode.png'])->saveQuietly();
    app(ResponsiveImageVariants::class)->generate('episodes/episode.png');

    pendingCommand('media:verify-responsive-images')
        ->expectsOutputToContain('Episodes: 1 checked, 0 verified, 1 failed.')
        ->assertFailed();
});
