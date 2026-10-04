<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Episode;
use App\Services\ResponsiveImageWorkflow;
use App\Services\SquareResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('episodes:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate square responsive WebP variants for existing episode images')]
class GenerateEpisodeImageVariants extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(SquareResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed] = $workflow->generate(
            Episode::class,
            'featured_image_path',
            'episode',
            (bool) $this->option('force'),
            $images,
            function (string $message): void {
                $this->warn($message);
            },
        );

        $noun = $generated === 1 ? 'episode' : 'episodes';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'episode' : 'episodes';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
