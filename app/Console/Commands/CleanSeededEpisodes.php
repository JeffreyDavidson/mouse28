<?php

namespace App\Console\Commands;

use App\Models\Episode;
use Illuminate\Console\Command;

class CleanSeededEpisodes extends Command
{
    protected $signature = 'episodes:clean-seeded';
    protected $description = 'Remove all seeded demo episodes';

    public function handle(): void
    {
        $count = Episode::count();

        if ($count === 0) {
            $this->info('No episodes to delete.');
            return;
        }

        Episode::truncate();

        $this->info("Deleted {$count} episodes.");
    }
}
