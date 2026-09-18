<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

#[Signature('content:generate-artwork {--type=posts : Cover type (posts or episodes)} {--id= : Generate only this published record} {--force : Allow generation in production}', aliases: ['content:generate-post-artwork'])]
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

        if ($this->option('id') !== null) {
            $id = filter_var($this->option('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false || ! (clone $query)->whereKey($id)->exists()) {
                $this->error('Choose an existing published record ID.');

                return self::FAILURE;
            }
            $query->whereKey($id);
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        if (config('filesystems.disks.public.driver') !== 'local' || (config('images.default') === 'gd' && ! function_exists('imagewebp'))) {
            $this->error('Artwork generation requires an image driver with WebP support and local public storage.');

            return self::FAILURE;
        }

        $generated = 0;
        $failed = false;

        foreach ($query->whereNotNull('cover_image')->select(['id', 'cover_image'])->lazyById(100) as $record) {
            $source = ResponsiveArtwork::source($record->cover_image);

            if (! $source) {
                $this->warn("Skipped unavailable or unsupported artwork for {$this->option('type')} record {$record->id}.");
                $failed = $failed || $this->option('id') !== null;

                continue;
            }

            try {
                $generated += $this->generate($source, $this->option('type') === 'episodes');
            } catch (RuntimeException) {
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
        $image = Image::fromPath($source['path']);
        [$sourceWidth, $sourceHeight] = $image->dimensions();

        if ($sourceWidth < 1 || $sourceHeight < 1 || $sourceWidth * $sourceHeight > 12_000_000) {
            throw new RuntimeException('Unsupported image dimensions.');
        }

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

            $contents = $image
                ->scale(width: $width)
                ->toWebp()
                ->quality(82)
                ->toBytes();

            if ($contents === '') {
                throw new RuntimeException('Empty encoded image.');
            }

            if (! $disk->put($path, $contents)) {
                throw new RuntimeException('Unable to write image.');
            }

            $generated++;
        }

        return $generated;
    }
}
