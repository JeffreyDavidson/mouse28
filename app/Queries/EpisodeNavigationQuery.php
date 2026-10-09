<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Episode;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads the published episodes either side of an episode in its own podcast, by publication
 * date with ties broken by id, selecting only the columns the navigation links render.
 */
final class EpisodeNavigationQuery
{
    /** @return array{previous: Episode|null, next: Episode|null} */
    public function get(Episode $episode): array
    {
        if ($episode->published_at === null) {
            return [
                'previous' => null,
                'next' => null,
            ];
        }

        return [
            'previous' => $this->publishedNeighbours($episode)
                ->where(fn (Builder $query) => $query->where('published_at', '<', $episode->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $episode->published_at)
                        ->where('id', '<', $episode->id)))
                ->latest('published_at')
                ->latest('id')
                ->first(),
            'next' => $this->publishedNeighbours($episode)
                ->where(fn (Builder $query) => $query->where('published_at', '>', $episode->published_at)
                    ->orWhere(fn (Builder $query) => $query->where('published_at', $episode->published_at)
                        ->where('id', '>', $episode->id)))
                ->oldest('published_at')
                ->oldest('id')
                ->first(),
        ];
    }

    /** @return Builder<Episode> */
    private function publishedNeighbours(Episode $episode): Builder
    {
        return Episode::published()
            ->select(['id', 'slug', 'title'])
            ->where('podcast_id', $episode->podcast_id);
    }
}
