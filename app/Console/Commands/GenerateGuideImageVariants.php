<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Guide;
use App\Services\ResponsiveImageVariants;
use App\Services\ResponsiveImageWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Signature('guides:generate-image-variants {--force : Regenerate variants that already pass verification}')]
#[Description('Generate responsive WebP variants for existing guide images')]
class GenerateGuideImageVariants extends Command implements Isolatable
{
    #[\Override]
    protected $isolated = true;

    #[\Override]
    protected $isolatedExitCode = self::FAILURE;

    public function handle(ResponsiveImageVariants $images, ResponsiveImageWorkflow $workflow): int
    {
        ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed] = $workflow->generate(
            Guide::class,
            'featured_image_path',
            'guide',
            (bool) $this->option('force'),
            $images,
            function (string $message): void {
                $this->warn($message);
            },
        );

        $noun = $generated === 1 ? 'guide' : 'guides';
        $this->info("Generated responsive images for {$generated} {$noun}.");

        if ($skipped > 0) {
            $skippedNoun = $skipped === 1 ? 'guide' : 'guides';
            $this->line("Skipped {$skipped} already verified {$skippedNoun}.");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
