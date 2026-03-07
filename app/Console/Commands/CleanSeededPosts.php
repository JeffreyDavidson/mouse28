<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class CleanSeededPosts extends Command
{
    protected $signature = 'posts:clean-seeded';
    protected $description = 'Remove seeded demo blog posts, keep real ones';

    public function handle(): void
    {
        $seededSlugs = [
            '10-quiet-spots-disney-world-kid-needs-break',
            'how-we-applied-das-pass-what-we-wish-we-knew',
            'why-we-go-disney-every-week',
            'understanding-autism-disney-what-families-should-know',
            'top-5-character-interactions-sensory-sensitive-kids',
            'recap-epcot-kids-think-differently-ep4',
            'meltdown-in-fantasyland-why-we-share-hard-parts',
            'best-disney-snacks-sensory-food-issues',
        ];

        $deleted = Post::whereIn('slug', $seededSlugs)->delete();
        $this->info("Deleted {$deleted} seeded blog posts.");
    }
}
