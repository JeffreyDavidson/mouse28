<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

#[Signature('content:attach-artwork')]
#[Description('Attach the bundled Mouse28 artwork to matching content without replacing uploads')]
class AttachContentArtwork extends Command
{
    /** @var array<string, string> */
    private const POST_ARTWORK = [
        'our-disney-park-bag-essentials' => 'posts/our-disney-park-bag-essentials.webp',
        'disney-dining-with-a-picky-eater' => 'posts/disney-dining-with-a-picky-eater.webp',
        'the-ride-that-surprised-us' => 'posts/the-ride-that-surprised-us.webp',
        'what-is-das-and-how-it-changed-our-disney-visits' => 'posts/what-is-das-and-how-it-changed-our-disney-visits.webp',
        'a-first-timers-guide-to-disney-world-with-a-sensory-sensitive-child' => 'posts/a-first-timers-guide-to-disney-world-with-a-sensory-sensitive-child.webp',
    ];

    /** @var array<string, string> */
    private const EPISODE_ARTWORK = [
        'trailer-meet-mouse28' => 'episodes/trailer-meet-mouse28.webp',
        'meet-jeffrey-and-cassie-our-disney-story' => 'episodes/meet-jeffrey-and-cassie-our-disney-story.webp',
    ];

    public function handle(): int
    {
        $artwork = [...self::POST_ARTWORK, ...self::EPISODE_ARTWORK];
        $sourceDirectory = (string) config('mouse28.content_artwork_path');
        $missingFiles = collect($artwork)->reject(
            fn (string $path): bool => File::isFile("{$sourceDirectory}/{$path}"),
        );

        if ($missingFiles->isNotEmpty()) {
            $this->error('Bundled artwork files are missing: '.$missingFiles->implode(', '));

            return self::FAILURE;
        }

        $copied = collect($artwork)->sum(function (string $path) use ($sourceDirectory): int {
            if (Storage::disk('public')->exists($path)) {
                return 0;
            }

            return Storage::disk('public')->put($path, File::get("{$sourceDirectory}/{$path}")) ? 1 : 0;
        });

        $updated = $this->attach(Post::query(), self::POST_ARTWORK)
            + $this->attach(Episode::query(), self::EPISODE_ARTWORK);

        $this->info("Copied {$copied} artwork files and attached artwork to {$updated} content records.");

        return self::SUCCESS;
    }

    /**
     * @param  Builder<Post>|Builder<Episode>  $query
     * @param  array<string, string>  $artwork
     */
    private function attach(Builder $query, array $artwork): int
    {
        return collect($artwork)->sum(fn (string $path, string $slug): int => (int) (clone $query)
            ->where('slug', $slug)
            ->where(function (Builder $query): void {
                $query->whereNull('cover_image')->orWhere('cover_image', '');
            })
            ->update(['cover_image' => $path]));
    }
}
