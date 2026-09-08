<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use ErrorException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('content:generate-artwork {--type=posts : Cover type (posts or episodes)} {--force : Allow generation in production}', aliases: ['content:generate-post-artwork'])]
#[Description('Generate static responsive WebP copies of published covers without replacing originals')]
class GenerateResponsiveArtwork extends Command
{
    use ConfirmableTrait;

    public function handle(): int
    {
        $query = match ($this->option('type')) {
            'posts' => Post::published(),
            'episodes' => Episode::published(),
            default => null,
        };

        if (! $query) {
            $this->error('Choose --type=posts or --type=episodes.');

            return self::FAILURE;
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        if (! function_exists('imagewebp') || config('filesystems.disks.public.driver') !== 'local') {
            $this->error('Artwork generation requires GD with WebP support and local public storage.');

            return self::FAILURE;
        }

        $generated = 0;
        $failed = false;

        foreach ($query->whereNotNull('cover_image')->select(['id', 'cover_image'])->lazyById(100) as $record) {
            $source = ResponsiveArtwork::source($record->cover_image);

            if (! $source) {
                $this->warn("Skipped unavailable or unsupported artwork for {$this->option('type')} record {$record->id}.");

                continue;
            }

            try {
                $generated += $this->generate($source, $this->option('type') === 'episodes');
            } catch (ErrorException|RuntimeException) {
                $this->error("Could not generate artwork for {$this->option('type')} record {$record->id}; original retained.");
                $failed = true;
            }
        }

        $this->info("Generated {$generated} responsive images. Originals and content records were not changed.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @param array{path: string, hash: string} $source */
    private function generate(array $source, bool $square): int
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

            foreach (ResponsiveArtwork::WIDTHS as $width) {
                $path = ResponsiveArtwork::variantPath($source['hash'], $width, $square);

                if ($width > ($square ? min($dimensions[0], $dimensions[1]) : $dimensions[0]) || $disk->exists($path)) {
                    continue;
                }

                if ($image === null) {
                    $image = imagecreatefromstring(File::get($source['path']));

                    if ($image && $square) {
                        $side = min($dimensions[0], $dimensions[1]);
                        $image = imagecrop($image, [
                            'x' => intdiv($dimensions[0] - $side, 2),
                            'y' => intdiv($dimensions[1] - $side, 2),
                            'width' => $side,
                            'height' => $side,
                        ]);
                    }
                }

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
