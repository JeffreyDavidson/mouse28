<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Podcast;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageWorkflow;

#[Signature('podcasts:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate responsive WebP variants for existing podcast cover images')]
class GeneratePodcastImageVariants extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed] = $workflow->generate(
            Podcast::class,
            'cover_image_path',
            'podcast',
            (bool) $this->option('force'),
            function (string $message): void {
                $this->warn($message);
            },
            $images,
        );

        $noun = $generated === 1 ? 'podcast' : 'podcasts';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'podcast' : 'podcasts';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
