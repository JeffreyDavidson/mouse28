<?php

use App\Models\Guide;
use App\Observers\GuideObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(GuideObserver::class);

test('guide images get variants that follow the image through replacement and deletion', function (): void {
    $image = UploadedFile::fake()
        ->image('guide.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('guides/old.png', $image);
    Storage::disk('public')->put('guides/new.png', $image);

    $guide = Guide::factory()->create(['featured_image_path' => 'guides/old.png']);

    Storage::disk('public')->assertExists(['guides/responsive/old-480.webp', 'guides/responsive/old-1280.webp']);

    $guide->update(['featured_image_path' => 'guides/new.png']);

    Storage::disk('public')->assertMissing(['guides/responsive/old-480.webp', 'guides/responsive/old-1280.webp']);
    Storage::disk('public')->assertExists(['guides/responsive/new-480.webp', 'guides/responsive/new-1280.webp']);

    $guide->forceDelete();

    Storage::disk('public')->assertMissing(['guides/responsive/new-480.webp', 'guides/responsive/new-1280.webp']);
});

test('a failed guide generation logs a warning naming the guide command', function (): void {
    Storage::disk('public')->put('guides/broken.png', 'not an image');
    Log::shouldReceive('warning')
        ->once()
        ->with('Responsive guide image generation failed. Run guides:generate-image-variants to retry.');

    Guide::factory()->create(['featured_image_path' => 'guides/broken.png']);
});
