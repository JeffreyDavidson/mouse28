<?php

use App\Console\Commands\GeneratePodcastImageVariants;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(GeneratePodcastImageVariants::class);

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

test('it backfills variants for the podcast cover and skips it once verified', function (): void {
    Storage::disk('public')->put('podcast/cover.png', UploadedFile::fake()->image('cover.png', 700, 700)->getContent());
    Podcast::settings()->forceFill(['cover_image_path' => 'podcast/cover.png'])->saveQuietly();

    pendingCommand('podcasts:generate-image-variants')
        ->expectsOutputToContain('Generated responsive images for 1 podcast.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists(['podcast/responsive/cover-480.webp', 'podcast/responsive/cover-640.webp']);

    pendingCommand('podcasts:generate-image-variants')
        ->expectsOutputToContain('Skipped 1 already verified podcast.')
        ->assertSuccessful();
});
