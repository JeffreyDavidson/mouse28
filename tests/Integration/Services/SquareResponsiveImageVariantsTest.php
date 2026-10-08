<?php

use App\Services\SquareResponsiveImageVariants;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

test('it generates centered square variants up to the shorter side of a wide original', function (): void {
    Storage::disk('public')->put('episodes/cover.png', UploadedFile::fake()
        ->image('cover.png', 1731, 909)
        ->getContent());

    $generated = app(SquareResponsiveImageVariants::class)->generate('episodes/cover.png');

    expect($generated)->toBeTrue();

    foreach ([480, 640, 768] as $width) {
        expect(getimagesize(Storage::disk('public')->path("episodes/responsive/cover-{$width}.webp")))
            ->toMatchArray([0 => $width, 1 => $width, 'mime' => 'image/webp']);
    }

    Storage::disk('public')->assertMissing('episodes/responsive/cover-1280.webp');
    Storage::disk('public')->assertExists('episodes/cover.png');
});

test('it crops the center of the original', function (): void {
    $image = imagecreatetruecolor(300, 100);
    imagefilledrectangle($image, 0, 0, 99, 99, (int) imagecolorallocate($image, 255, 0, 0));
    imagefilledrectangle($image, 100, 0, 199, 99, (int) imagecolorallocate($image, 0, 0, 255));
    imagefilledrectangle($image, 200, 0, 299, 99, (int) imagecolorallocate($image, 255, 0, 0));
    ob_start();
    imagepng($image);
    Storage::disk('public')->put('episodes/center.png', (string) ob_get_clean());
    config()->set('media.responsive_widths', [100]);

    app(SquareResponsiveImageVariants::class)->generate('episodes/center.png');

    $variant = imagecreatefromstring((string) Storage::disk('public')->get('episodes/responsive/center-100.webp'))
        ?: throw new UnexpectedValueException('The square variant could not be decoded.');
    $pixel = imagecolorsforindex($variant, (int) imagecolorat($variant, 50, 50));
    expect($pixel['blue'])->toBeGreaterThan(200)
        ->and($pixel['red'])
        ->toBeLessThan(50);
});

test('it verifies square variants against the shorter side of the original', function (): void {
    Storage::disk('public')->put('episodes/cover.png', UploadedFile::fake()
        ->image('cover.png', 1000, 700)
        ->getContent());
    $images = app(SquareResponsiveImageVariants::class);

    expect($images->hasRequiredVariants('episodes/cover.png'))->toBeFalse();

    $images->generate('episodes/cover.png');

    expect($images->hasRequiredVariants('episodes/cover.png'))->toBeTrue();

    Storage::disk('public')->put('episodes/responsive/cover-768.webp', 'obsolete');

    expect($images->hasRequiredVariants('episodes/cover.png'))->toBeFalse();
});

test('it rejects an unsupported original', function (): void {
    Storage::disk('public')->put('episodes/broken.png', 'not an image');
    $images = app(SquareResponsiveImageVariants::class);

    expect($images->generate('episodes/broken.png'))->toBeFalse()
        ->and($images->hasRequiredVariants('episodes/broken.png'))
        ->toBeFalse()
        ->and($images->hasRequiredVariants('episodes/missing.png'))
        ->toBeFalse();
});
