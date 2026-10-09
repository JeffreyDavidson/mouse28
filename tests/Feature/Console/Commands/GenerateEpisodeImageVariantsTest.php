<?php

use App\Console\Commands\GenerateEpisodeImageVariants;
use App\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(GenerateEpisodeImageVariants::class);

pest()->use(RefreshDatabase::class);

test('it backfills square variants for episode images, skips verified episodes and regenerates with force', function (): void {
    Storage::disk('public')->put('episodes/episode.png', UploadedFile::fake()
        ->image('episode.png', 1000, 700)
        ->getContent());
    Episode::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'episodes/episode.png'])
        ->saveQuietly();

    pendingCommand('episodes:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 episode.')
        ->assertSuccessful();

    expect(getimagesize(Storage::disk('public')->path('episodes/responsive/episode-640.webp')))
        ->toMatchArray([0 => 640, 1 => 640]);
    Storage::disk('public')->assertMissing('episodes/responsive/episode-768.webp');

    pendingCommand('episodes:generate-image-variants')
        ->expectsOutputToContain('Skipped 1 already verified episode.')
        ->assertSuccessful();

    pendingCommand('episodes:generate-image-variants', ['--force' => true])
        ->expectsOutputToContain('Generated responsive images for 1 episode.')
        ->assertSuccessful();
});

test('it fails when an episode image is missing', function (): void {
    Episode::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'episodes/missing.png'])
        ->saveQuietly();

    pendingCommand('episodes:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 episodes.')
        ->assertFailed();
});
