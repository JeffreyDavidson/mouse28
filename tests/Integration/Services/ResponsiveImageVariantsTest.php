<?php

use App\Services\ResponsiveImageVariants;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

function storeResponsiveSource(string $path, int $width, int $height): void
{
    Storage::disk('public')->put($path, UploadedFile::fake()->image('source.png', $width, $height)->getContent());
}

test('it generates a webp variant for every configured width without replacing the original', function (): void {
    storeResponsiveSource('posts/cover.png', 1280, 72);

    $generated = app(ResponsiveImageVariants::class)->generate('posts/cover.png');

    expect($generated)->toBeTrue();
    Storage::disk('public')->assertExists('posts/cover.png');

    foreach ([480 => 27, 640 => 36, 768 => 43, 1280 => 72] as $width => $height) {
        expect(getimagesize(Storage::disk('public')->path("posts/responsive/cover-{$width}.webp")))
            ->toMatchArray([0 => $width, 1 => $height, 'mime' => 'image/webp']);
    }
});

test('it names variants after the original in a responsive directory', function (string $original, array $expected): void {
    expect(app(ResponsiveImageVariants::class)->paths($original))->toBe($expected);
})->with([
    'a nested path' => ['posts/my-post.webp', [
        480 => 'posts/responsive/my-post-480.webp',
        640 => 'posts/responsive/my-post-640.webp',
        768 => 'posts/responsive/my-post-768.webp',
        1280 => 'posts/responsive/my-post-1280.webp',
    ]],
    'a root path' => ['cover.jpg', [
        480 => 'responsive/cover-480.webp',
        640 => 'responsive/cover-640.webp',
        768 => 'responsive/cover-768.webp',
        1280 => 'responsive/cover-1280.webp',
    ]],
]);

test('it builds variants for the widths configured for the site', function (): void {
    config()->set('media.responsive_widths', [320, 900]);
    storeResponsiveSource('posts/cover.png', 1280, 72);

    app(ResponsiveImageVariants::class)->generate('posts/cover.png');

    Storage::disk('public')->assertExists(['posts/responsive/cover-320.webp', 'posts/responsive/cover-900.webp']);
    Storage::disk('public')->assertMissing(['posts/responsive/cover-480.webp', 'posts/responsive/cover-1280.webp']);
});

test('it never upscales and removes a stale variant wider than the original', function (): void {
    storeResponsiveSource('posts/small.png', 700, 40);
    Storage::disk('public')->put('posts/responsive/small-1280.webp', 'stale');

    expect(app(ResponsiveImageVariants::class)->generate('posts/small.png'))->toBeTrue();

    Storage::disk('public')->assertExists(['posts/responsive/small-480.webp', 'posts/responsive/small-640.webp']);
    Storage::disk('public')->assertMissing(['posts/responsive/small-768.webp', 'posts/responsive/small-1280.webp']);
});

test('it reports missing and unsupported originals as failures', function (string $path): void {
    Storage::disk('public')->put('posts/broken.png', 'not an image');

    expect(app(ResponsiveImageVariants::class)->generate($path))->toBeFalse()
        ->and(app(ResponsiveImageVariants::class)->hasRequiredVariants($path))->toBeFalse();
})->with(['a missing original' => 'posts/missing.png', 'an unsupported original' => 'posts/broken.png']);

test('it keeps existing variants when writing a replacement fails', function (): void {
    $contents = UploadedFile::fake()->image('cover.png', 1280, 72)->getContent();
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->expects('exists')->with('posts/cover.png')->andReturnTrue();
    $disk->expects('get')->with('posts/cover.png')->andReturn($contents);
    $disk->expects('put')->andReturnFalse();
    $disk->shouldNotReceive('delete');
    Storage::shouldReceive('disk')->with('public')->andReturn($disk);

    expect(app(ResponsiveImageVariants::class)->generate('posts/cover.png'))->toBeFalse();
});

test('it verifies every variant appropriate for the source width', function (): void {
    storeResponsiveSource('posts/cover.png', 700, 40);
    $images = app(ResponsiveImageVariants::class);

    expect($images->hasRequiredVariants('posts/cover.png'))->toBeFalse();

    $images->generate('posts/cover.png');

    expect($images->hasRequiredVariants('posts/cover.png'))->toBeTrue();

    Storage::disk('public')->put('posts/responsive/cover-1280.webp', 'obsolete');

    expect($images->hasRequiredVariants('posts/cover.png'))->toBeFalse();

    Storage::disk('public')->delete('posts/responsive/cover-1280.webp');
    Storage::disk('public')->put('posts/responsive/cover-640.webp', 'not an image');

    expect($images->hasRequiredVariants('posts/cover.png'))->toBeFalse();
});

test('it deletes the variants without deleting the original', function (): void {
    Storage::disk('public')->put('posts/cover.png', 'original');
    Storage::disk('public')->put('posts/responsive/cover-480.webp', 'small');
    Storage::disk('public')->put('posts/responsive/cover-1280.webp', 'large');

    app(ResponsiveImageVariants::class)->delete('posts/cover.png');

    Storage::disk('public')->assertMissing(['posts/responsive/cover-480.webp', 'posts/responsive/cover-1280.webp']);
    Storage::disk('public')->assertExists('posts/cover.png');
});

test('the srcset lists only the variants that exist and never the original', function (): void {
    Storage::disk('public')->put('posts/cover.png', 'original');
    Storage::disk('public')->put('posts/responsive/cover-480.webp', 'small');
    Storage::disk('public')->put('posts/responsive/cover-768.webp', 'medium');
    $disk = Storage::disk('public');

    expect(app(ResponsiveImageVariants::class)->srcset('posts/cover.png'))
        ->toBe("{$disk->url('posts/responsive/cover-480.webp')} 480w, {$disk->url('posts/responsive/cover-768.webp')} 768w");
});

test('the srcset is null without a path or without variants', function (?string $path): void {
    expect(app(ResponsiveImageVariants::class)->srcset($path))->toBeNull();
})->with(['no path' => null, 'an empty path' => '', 'no variants' => 'posts/cover.png']);
