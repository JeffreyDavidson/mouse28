<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

final readonly class ResponsiveImageRepairWorkflow
{
    public function __construct(
        private readonly ResponsiveImageVariants $images,
        private readonly SquareResponsiveImageVariants $squareImages,
        private readonly ResponsiveImageWorkflow $workflow,
    ) {}

    /**
     * @return array{
     *     generations: array<string, array{generated: int, skipped: int, failed: int}>,
     *     verification: array<string, array{checked: int, failed: int}>,
     *     warnings: list<string>,
     * }
     */
    public function repair(bool $force): array
    {
        $warnings = [];
        $generations = [];

        foreach ($this->resources() as $resource) {
            $generations[$resource['label']] = $this->workflow->generate(
                $resource['class'],
                $resource['column'],
                $resource['label'],
                $force,
                $resource['images'],
                function (string $warning) use (&$warnings): void {
                    $warnings[] = $warning;
                },
            );
        }

        $verification = [];

        foreach ($this->resources() as $resource) {
            $verification[$resource['label']] = $this->workflow->verify(
                $resource['class'],
                $resource['column'],
                $resource['images'],
            );
        }

        return [
            'generations' => $generations,
            'verification' => $verification,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return list<array{class: class-string<Model>, column: string, label: string, images: ResponsiveImageVariants}>
     */
    private function resources(): array
    {
        return [
            ['class' => Post::class, 'column' => 'featured_image_path', 'label' => 'post', 'images' => $this->images],
            ['class' => Episode::class, 'column' => 'featured_image_path', 'label' => 'episode', 'images' => $this->squareImages],
            ['class' => Guide::class, 'column' => 'featured_image_path', 'label' => 'guide', 'images' => $this->images],
            ['class' => Podcast::class, 'column' => 'cover_image_path', 'label' => 'podcast', 'images' => $this->images],
        ];
    }
}
