<?php

use App\Console\Commands\GenerateGuideImageVariants;
use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(GenerateGuideImageVariants::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

test('it backfills variants for guide images and skips verified guides', function (): void {
    Storage::disk('public')->put('guides/guide.png', UploadedFile::fake()
        ->image('guide.png', 800, 45)
        ->getContent());
    Guide::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'guides/guide.png'])
        ->saveQuietly();

    pendingCommand('guides:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 guide.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists(['guides/responsive/guide-480.webp', 'guides/responsive/guide-768.webp']);
    Storage::disk('public')->assertMissing('guides/responsive/guide-1280.webp');

    pendingCommand('guides:generate-image-variants')
        ->expectsOutputToContain('Skipped 1 already verified guide.')
        ->assertSuccessful();
});

test('it fails when a guide image cannot be decoded', function (): void {
    Storage::disk('public')->put('guides/broken.png', 'not an image');
    Guide::factory()
        ->create()
        ->forceFill(['featured_image_path' => 'guides/broken.png'])
        ->saveQuietly();

    pendingCommand('guides:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 0 guides.')
        ->assertFailed();
});
