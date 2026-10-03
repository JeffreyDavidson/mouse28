<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Services\ResponsiveImageVariants;
use App\Services\ResponsiveImageWorkflow;
use App\Services\SquareResponsiveImageVariants;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:verify-responsive-images')]
#[Description('Verify responsive image variants for stored post, episode, guide, and podcast media')]
class VerifyResponsiveImages extends Command
{
    public function handle(ResponsiveImageVariants $images, SquareResponsiveImageVariants $squareImages, ResponsiveImageWorkflow $workflow): int
    {
        $post = $workflow->verify(Post::class, 'featured_image_path', $images);
        $episode = $workflow->verify(Episode::class, 'featured_image_path', $squareImages);
        $guide = $workflow->verify(Guide::class, 'featured_image_path', $images);
        $podcast = $workflow->verify(Podcast::class, 'cover_image_path', $images);

        $results = [
            'Posts' => [$post['checked'], $post['failed']],
            'Episodes' => [$episode['checked'], $episode['failed']],
            'Guides' => [$guide['checked'], $guide['failed']],
            'Podcasts' => [$podcast['checked'], $podcast['failed']],
        ];

        foreach ($results as $label => [$checked, $failed]) {
            $verified = $checked - $failed;

            $this->line("{$label}: {$checked} checked, {$verified} verified, {$failed} failed.");
        }

        $failures = $post['failed'] + $episode['failed'] + $guide['failed'] + $podcast['failed'];

        if ($failures > 0) {
            $this->error('Responsive image verification failed.');

            return self::FAILURE;
        }

        $this->info('Responsive image verification passed.');

        return self::SUCCESS;
    }
}
