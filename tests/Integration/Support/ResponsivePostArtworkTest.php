<?php

use App\Support\ResponsivePostArtwork;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('unsafe or unavailable source paths have no responsive candidates', function (?string $path): void {
    Storage::fake('public');

    expect(ResponsivePostArtwork::srcset($path))->toBeNull();
})->with([null, '../private.png', 'posts/../../private.png', 'https://example.com/cover.png', 'posts/missing.png']);

test('responsive artwork retains the original for larger displays and omits missing variants', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $contents = UploadedFile::fake()->image('cover.png', 600, 300)->getContent();
    $disk->put('posts/cover.png', $contents);
    $variant = ResponsivePostArtwork::variantPath(hash('sha256', $contents), 480);
    $disk->put($variant, 'generated image');

    expect(ResponsivePostArtwork::srcset('posts/cover.png'))
        ->toBe($disk->url($variant).' 480w, '.$disk->url('posts/cover.png').' 600w');

    $disk->delete($variant);

    expect(ResponsivePostArtwork::srcset('posts/cover.png'))->toBeNull();
});
