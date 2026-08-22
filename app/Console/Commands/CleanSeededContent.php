<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('content:clean-seeded {--force : Allow demo content cleanup in production}')]
#[Description('Remove only the known demo posts, guides, and episodes')]
class CleanSeededContent extends Command
{
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Production cleanup requires the --force option.');

            return self::FAILURE;
        }

        [$posts, $guides, $episodes] = DB::transaction(function (): array {
            $seededEpisodeIds = Episode::query()
                ->whereIn('slug', self::episodeSlugs())
                ->pluck('id');

            Post::query()
                ->whereIn('episode_id', $seededEpisodeIds)
                ->update(['episode_id' => null]);

            return [
                Post::query()->whereIn('slug', self::postSlugs())->forceDelete(),
                Guide::query()->whereIn('slug', self::guideSlugs())->forceDelete(),
                Episode::query()->whereIn('slug', self::episodeSlugs())->forceDelete(),
            ];
        });

        $this->info("Removed {$posts} demo posts, {$guides} demo guides, and {$episodes} demo episodes.");

        return self::SUCCESS;
    }

    /** @return list<string> */
    public static function postSlugs(): array
    {
        return [
            '10-quiet-spots-disney-world-kid-needs-break',
            'how-we-applied-das-pass-what-we-wish-we-knew',
            'why-we-go-disney-every-week',
            'understanding-autism-disney-what-families-should-know',
            'top-5-character-interactions-sensory-sensitive-kids',
            'recap-epcot-kids-think-differently-ep4',
            'meltdown-in-fantasyland-why-we-share-hard-parts',
            'best-disney-snacks-sensory-food-issues',
        ];
    }

    /** @return list<string> */
    public static function guideSlugs(): array
    {
        return [
            'understanding-das-pass',
            'sensory-tips-magic-kingdom',
            'quiet-spots-magic-kingdom',
            'epcot-best-park-neurodiverse',
            'accessible-dining-walt-disney-world',
            'hollywood-studios-ride-guide',
            'animal-kingdom-sensory-friendly-trails',
            'planning-first-accessible-disney-trip',
            'quiet-spots-epcot',
            'magic-kingdom-rides-accessibility',
        ];
    }

    /** @return list<string> */
    public static function episodeSlugs(): array
    {
        return [
            'welcome-to-mouse28',
            'das-pass-everything-you-need-to-know',
            'sensory-friendly-spots-magic-kingdom',
            'epcot-with-kids-who-think-differently',
        ];
    }
}
