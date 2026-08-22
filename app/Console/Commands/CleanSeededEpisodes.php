<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Remove only the known seeded demo episodes')]
#[Signature('episodes:clean-seeded {--force : Allow demo content cleanup in production}')]
class CleanSeededEpisodes extends Command
{
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Production cleanup requires the --force option.');

            return self::FAILURE;
        }

        $seededEpisodeIds = Episode::query()
            ->whereIn('slug', CleanSeededContent::episodeSlugs())
            ->pluck('id');
        $count = $seededEpisodeIds->count();

        if ($count === 0) {
            $this->info('No episodes to delete.');

            return self::SUCCESS;
        }

        Post::query()
            ->whereIn('episode_id', $seededEpisodeIds)
            ->update(['episode_id' => null]);

        Episode::query()
            ->whereIn('id', $seededEpisodeIds)
            ->forceDelete();

        $this->info("Deleted {$count} seeded episodes.");

        return self::SUCCESS;
    }
}
