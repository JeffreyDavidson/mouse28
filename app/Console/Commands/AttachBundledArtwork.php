<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use App\Services\SquareResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo;

#[Signature('content:attach-bundled-artwork', aliases: ['content:attach-artwork'])]
#[Description('Attach the bundled Mouse28 artwork to matching content without replacing uploads')]
class AttachBundledArtwork extends Command
{
    private int $failedVariants = 0;

    public function handle(ResponsiveImageVariants $images, SquareResponsiveImageVariants $squareImages): int
    {
        $sourceDirectory = Config::string('mouse28.content_artwork_path');

        foreach (['posts', 'episodes'] as $type) {
            if (! File::isDirectory("{$sourceDirectory}/{$type}")) {
                $this->error("Bundled artwork directory is missing: {$sourceDirectory}/{$type}");

                return self::FAILURE;
            }
        }

        $postArtwork = $this->artwork($sourceDirectory, 'posts');
        $episodeArtwork = $this->artwork($sourceDirectory, 'episodes');
        $artwork = [...array_values($postArtwork), ...array_values($episodeArtwork)];
        $missingFiles = collect($artwork)->reject(
            fn (string $path): bool => File::isFile("{$sourceDirectory}/{$path}"),
        );

        if ($missingFiles->isNotEmpty()) {
            $this->error('Bundled artwork files are missing: '.$missingFiles->implode(', '));

            return self::FAILURE;
        }

        $copied = 0;

        foreach ($artwork as $path) {
            if (Storage::disk('public')->exists($path)) {
                continue;
            }

            if (! Storage::disk('public')->put($path, File::get("{$sourceDirectory}/{$path}"))) {
                $this->error("Unable to copy artwork: {$path}. No content records were updated.");

                return self::FAILURE;
            }

            $copied++;
        }

        $updated = $this->attach(Post::query(), $postArtwork, $images)
            + $this->attach(Episode::query(), $episodeArtwork, $squareImages);

        $this->info("Copied {$copied} artwork files and attached artwork to {$updated} content records.");

        if ($this->failedVariants > 0) {
            $this->error("Responsive images could not be generated for {$this->failedVariants} attached artwork files. Run media:repair-responsive-images to retry.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** @return array<string, string> */
    private function artwork(string $sourceDirectory, string $type): array
    {
        return collect(File::files("{$sourceDirectory}/{$type}"))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'webp')
            ->mapWithKeys(fn (SplFileInfo $file): array => [
                $file->getBasename('.webp') => "{$type}/{$file->getFilename()}",
            ])
            ->all();
    }

    /**
     * Fills `featured_image_path` only where it is empty, then generates the responsive
     * variants for every file it attached. The query update skips model events, so the
     * variants are generated here rather than by the model observers.
     *
     * @param  Builder<Post>|Builder<Episode>  $query
     * @param  array<string, string>  $artwork
     */
    private function attach(Builder $query, array $artwork, ResponsiveImageVariants $images): int
    {
        $updated = 0;

        foreach ($artwork as $slug => $path) {
            $attached = (clone $query)
                ->where('slug', $slug)
                ->where(function (Builder $query): void {
                    $query->whereNull('featured_image_path')->orWhere('featured_image_path', '');
                })
                ->update(['featured_image_path' => $path]);

            if ($attached > 0 && ! $images->generate($path)) {
                $this->failedVariants++;
            }

            $updated += $attached;
        }

        return $updated;
    }
}
