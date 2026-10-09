<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Podcast;
use Illuminate\Support\Facades\Config;

/**
 * Mouse28 runs one show of the multi-show podcast model. The oldest podcast row is that
 * show; its default values come from `podcast.default_show`. Bound as a scoped service,
 * so `current()` reads the database once per request or job.
 */
final class PrimaryPodcast
{
    private ?Podcast $current = null;

    /** The show for public pages; unsaved defaults when no show exists yet. */
    public function current(): Podcast
    {
        return $this->current ??= $this->find() ?? new Podcast($this->defaults());
    }

    /** The saved show, created from the defaults when no show exists yet. */
    public function findOrCreate(): Podcast
    {
        return $this->find() ?? Podcast::create($this->defaults());
    }

    private function find(): ?Podcast
    {
        return Podcast::query()
            ->oldest('id')
            ->first();
    }

    /** @return array{name: string, slug: string, description: string} */
    private function defaults(): array
    {
        return [
            'name' => Config::string('podcast.default_show.name'),
            'slug' => Config::string('podcast.default_show.slug'),
            'description' => Config::string('podcast.default_show.description'),
        ];
    }
}
