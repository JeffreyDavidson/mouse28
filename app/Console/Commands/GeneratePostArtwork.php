<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\ResponsivePostArtwork;
use ErrorException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('content:generate-post-artwork {--force : Allow generation in production}')]
#[Description('Generate static responsive WebP copies of published post covers without replacing originals')]
class GeneratePostArtwork extends Command
{
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Production artwork generation requires explicit --force approval.');

            return self::FAILURE;
        }

        if (! function_exists('imagewebp') || config('filesystems.disks.public.driver') !== 'local') {
            $this->error('Artwork generation requires GD with WebP support and local public storage.');

            return self::FAILURE;
        }

        $generated = 0;
        $failed = false;

        foreach (Post::published()->whereNotNull('cover_image')->select(['id', 'cover_image'])->lazyById(100) as $post) {
            $source = ResponsivePostArtwork::source($post->cover_image);

            if (! $source) {
                $this->warn("Skipped unavailable or unsupported artwork for post {$post->id}.");

                continue;
            }

            try {
                $generated += $this->generate($source);
            } catch (ErrorException|RuntimeException) {
                $this->error("Could not generate artwork for post {$post->id}; original retained.");
                $failed = true;
            }
        }

        $this->info("Generated {$generated} responsive images. Originals and content records were not changed.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @param array{path: string, hash: string} $source */
    private function generate(array $source): int
    {
        // Convert decoder warnings into a bounded per-image failure, without printing file contents.
        set_error_handler(static function (int $severity, string $message): never {
            throw new ErrorException($message, 0, $severity);
        });

        try {
            $dimensions = getimagesize($source['path']);

            if (! $dimensions || $dimensions[0] * $dimensions[1] > 12_000_000) {
                throw new RuntimeException('Unsupported image dimensions.');
            }

            $image = null;
            $generated = 0;
            $disk = Storage::disk('public');

            foreach (ResponsivePostArtwork::WIDTHS as $width) {
                $path = ResponsivePostArtwork::variantPath($source['hash'], $width);

                if ($width > $dimensions[0] || $disk->exists($path)) {
                    continue;
                }

                $image ??= imagecreatefromstring(File::get($source['path']));

                if (! $image) {
                    throw new RuntimeException('Unable to decode image.');
                }

                $resized = imagescale($image, $width);

                if (! $resized) {
                    throw new RuntimeException('Unable to resize image.');
                }

                imagesavealpha($resized, true);
                ob_start();

                try {
                    if (! imagewebp($resized, null, 82)) {
                        throw new RuntimeException('Unable to encode image.');
                    }

                    $contents = ob_get_contents();
                } finally {
                    ob_end_clean();
                }

                if (! is_string($contents) || $contents === '') {
                    throw new RuntimeException('Empty encoded image.');
                }

                File::ensureDirectoryExists(dirname($disk->path($path)));
                File::replace($disk->path($path), $contents);
                $generated++;
            }

            return $generated;
        } finally {
            restore_error_handler();
        }
    }
}
