<?php

use App\Support\ResponsiveArtwork;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('unsafe or unavailable source paths have no responsive candidates', function (?string $path): void {
    Storage::fake('public');

    expect(ResponsiveArtwork::srcset($path))->toBeNull();
})->with([null, '../private.png', 'posts/../../private.png', 'https://example.com/cover.png', 'posts/missing.png']);

test('responsive artwork retains the original for larger displays and omits missing variants', function (string $directory): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $contents = UploadedFile::fake()->image('cover.png', 600, 300)->getContent();
    $sourcePath = "{$directory}/cover.png";
    $disk->put($sourcePath, $contents);
    $variant = ResponsiveArtwork::variantPath(hash('sha256', $contents), 480);
    $disk->put($variant, 'generated image');

    expect(ResponsiveArtwork::srcset($sourcePath))
        ->toBe($disk->url($variant).' 480w, '.$disk->url($sourcePath).' 600w');

    $disk->delete($variant);

    expect(ResponsiveArtwork::srcset($sourcePath))->toBeNull();
})->with(['posts', 'episodes']);
