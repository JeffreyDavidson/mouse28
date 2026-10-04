<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;

/**
 * Responsive variants for images shown in square frames (mouse28 episode covers).
 *
 * The same `{dir}/responsive/{filename}-{width}.webp` paths, widths and WebP quality
 * as `ResponsiveImageVariants`, but each variant is a centered square crop of the
 * original, so a wide original is never stretched into a square frame. A width is
 * only generated when the original's shorter side covers it, so nothing is upscaled.
 * This class is mouse28-only: The Laravel Architect has no episode images.
 */
class SquareResponsiveImageVariants extends ResponsiveImageVariants
{
    #[\Override]
    public function generate(string $originalPath): bool
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($originalPath)) {
            return false;
        }

        $contents = $disk->get($originalPath);

        if ($contents === null) {
            return false;
        }

        try {
            $source = Image::fromBytes($contents);
            $sourceSide = min($source->width(), $source->height());
        } catch (ImageException) {
            return false;
        }

        $variantPaths = $this->paths($originalPath);
        $variants = [];

        try {
            foreach ($variantPaths as $width => $variantPath) {
                if ($width > $sourceSide) {
                    continue;
                }

                $variants[$variantPath] = $source
                    ->cover($width, $width)
                    ->toWebp()
                    ->quality(82)
                    ->toBytes();
            }
        } catch (ImageException) {
            return false;
        }

        foreach ($variants as $variantPath => $variant) {
            if (! $disk->put($variantPath, $variant)) {
                return false;
            }
        }

        $obsoletePaths = array_diff(array_values($variantPaths), array_keys($variants));

        if ($obsoletePaths !== [] && ! $disk->delete($obsoletePaths)) {
            return false;
        }

        return true;
    }

    /**
     * Square variants exist up to the original's shorter side rather than its width.
     */
    #[\Override]
    public function hasRequiredVariants(string $originalPath, ?int $sourceWidth = null): bool
    {
        if ($sourceWidth === null && Storage::disk('public')->exists($originalPath)) {
            try {
                $source = Image::fromStorage($originalPath, 'public');
                $sourceWidth = min($source->width(), $source->height());
            } catch (ImageException) {
                return false;
            }
        }

        return parent::hasRequiredVariants($originalPath, $sourceWidth);
    }
}
