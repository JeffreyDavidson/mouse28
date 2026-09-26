<?php

namespace App\Actions;

use App\Models\Episode;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GenerateResponsiveCover
{
    public function __invoke(Post|Episode $record): int
    {
        if (! $record->isLive()) {
            throw new RuntimeException('Only published covers can be generated.');
        }

        if (config('filesystems.disks.public.driver') !== 'local' || (config('images.default') === 'gd' && ! function_exists('imagewebp'))) {
            throw new RuntimeException('Artwork generation requires WebP support and local public storage.');
        }

        $source = ResponsiveArtwork::source($record->cover_image);
        if ($source === null) {
            throw new RuntimeException('The cover source is unavailable or unsupported.');
        }

        $image = Image::fromPath($source['path']);
        [$sourceWidth, $sourceHeight] = $image->dimensions();

        if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > 12_000_000) {
            throw new RuntimeException('Unsupported image dimensions.');
        }

        $square = $record instanceof Episode;
        if ($square) {
            $side = min($sourceWidth, $sourceHeight);
            $image = $image->crop($side, $side, intdiv($sourceWidth - $side, 2), intdiv($sourceHeight - $side, 2));
        }

        $generated = 0;
        $disk = Storage::disk('public');

        foreach (ResponsiveArtwork::WIDTHS as $width) {
            $path = ResponsiveArtwork::variantPath($source['hash'], $width, $square);

            if ($width > ($square ? min($sourceWidth, $sourceHeight) : $sourceWidth) || $disk->exists($path)) {
                continue;
            }

            $contents = $image->scale(width: $width)->toWebp()->quality(82)->toBytes();
            if ($contents === '') {
                throw new RuntimeException('Empty encoded image.');
            }

            // Rename on the same local disk publishes only a complete image.
            $temporaryPath = $path.'.'.Str::uuid().'.tmp';
            try {
                if (! $disk->put($temporaryPath, $contents) || ! $disk->move($temporaryPath, $path)) {
                    throw new RuntimeException('Unable to publish responsive image.');
                }
            } finally {
                $disk->delete($temporaryPath);
            }

            $generated++;
        }

        return $generated;
    }
}
