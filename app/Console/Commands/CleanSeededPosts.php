<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Remove seeded demo blog posts, keep real ones')]
#[Signature('posts:clean-seeded {--force : Allow demo content cleanup in production}')]
class CleanSeededPosts extends Command
{
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Production cleanup requires the --force option.');

            return self::FAILURE;
        }

        $deleted = Post::query()
            ->whereIn('slug', CleanSeededContent::postSlugs())
            ->delete();
        $this->info("Deleted {$deleted} seeded blog posts.");

        return self::SUCCESS;
    }
}
