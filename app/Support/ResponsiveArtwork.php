<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ResponsiveArtwork
{
    public const WIDTHS = [480, 640, 768, 1280];

    /** @return array{path: string, hash: string}|null */
    public static function source(?string $path): ?array
    {
        if (! $path || ! preg_match('#\A(?:posts|episodes)/[a-zA-Z0-9_/-]+\.(webp|png|jpe?g)\z#', $path)
            || config('filesystems.disks.public.driver') !== 'local') {
            return null;
        }

        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        $file = realpath($disk->path($path));

        if (! $root || ! $file || ! str_starts_with($file, $root.DIRECTORY_SEPARATOR)
            || ! is_file($file) || filesize($file) > 5 * 1024 * 1024) {
            return null;
        }

        $hash = hash_file('sha256', $file);

        return $hash === false ? null : ['path' => $file, 'hash' => $hash];
    }

    public static function variantPath(string $hash, int $width, bool $square = false): string
    {
        if ($square) {
            return "episodes/responsive/v1/{$hash}-{$width}.webp";
        }

        // Retain the shared content-addressed namespace so existing immutable URLs stay valid.
        return "posts/responsive/{$hash}-{$width}.webp";
    }

    public static function srcset(?string $path, bool $square = false): ?string
    {
        $source = self::source($path);

        if (! $source) {
            return null;
        }

        $disk = Storage::disk('public');
        $variants = [];

        foreach (self::WIDTHS as $width) {
            $variant = self::variantPath($source['hash'], $width, $square);

            if ($disk->exists($variant)) {
                $variants[] = $disk->url($variant)." {$width}w";
            }
        }

        if ($variants === []) {
            return null;
        }

        // Generated candidates prove these exact source bytes decoded successfully.
        // Keep the original available for larger/high-density displays.
        $dimensions = getimagesize($source['path']);

        if (! $square && $dimensions && ! $disk->exists(self::variantPath($source['hash'], $dimensions[0]))) {
            $variants[] = $disk->url($path)." {$dimensions[0]}w";
        }

        return implode(', ', $variants);
    }
}
