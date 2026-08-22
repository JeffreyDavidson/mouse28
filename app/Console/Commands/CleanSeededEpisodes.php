<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Remove all seeded demo episodes')]
#[Signature('episodes:clean-seeded')]
class CleanSeededEpisodes extends Command
{
    public function handle(): void
    {
        $count = Episode::count();

        if ($count === 0) {
            $this->info('No episodes to delete.');

            return;
        }

        // Null out episode references on posts first
        Post::whereNotNull('episode_id')->update(['episode_id' => null]);

        Episode::query()->delete();

        $this->info("Deleted {$count} episodes.");
    }
}
