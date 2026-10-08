<?php

use App\Models\Episode;
use App\Observers\EpisodeObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(EpisodeObserver::class);

test('episode images get square variants that follow the image through replacement and deletion', function (): void {
    $image = UploadedFile::fake()
        ->image('episode.png', 900, 500)
        ->getContent();
    Storage::disk('public')->put('episodes/old.png', $image);
    Storage::disk('public')->put('episodes/new.png', $image);

    $episode = Episode::factory()->create(['featured_image_path' => 'episodes/old.png']);

    expect(getimagesize(Storage::disk('public')->path('episodes/responsive/old-480.webp')))
        ->toMatchArray([0 => 480, 1 => 480]);
    Storage::disk('public')->assertMissing('episodes/responsive/old-640.webp');

    $episode->update(['featured_image_path' => 'episodes/new.png']);

    Storage::disk('public')->assertMissing('episodes/responsive/old-480.webp');
    Storage::disk('public')->assertExists('episodes/responsive/new-480.webp');

    $episode->forceDelete();

    Storage::disk('public')->assertMissing('episodes/responsive/new-480.webp');
});

test('a failed episode generation logs a warning naming the episode command', function (): void {
    Storage::disk('public')->put('episodes/broken.png', 'not an image');
    Log::shouldReceive('warning')
        ->once()
        ->with('Responsive episode image generation failed. Run episodes:generate-image-variants to retry.');

    Episode::factory()->create(['featured_image_path' => 'episodes/broken.png']);
});
