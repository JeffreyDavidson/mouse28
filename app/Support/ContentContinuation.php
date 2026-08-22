<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use Illuminate\Database\Eloquent\Collection;

class ContentContinuation
{
    /** @return Collection<int, Guide> */
    public static function relatedGuides(Guide $guide, int $limit = 3): Collection
    {
        $sameCategoryGuides = Guide::published()
            ->whereKeyNot($guide->getKey())
            ->where('category', $guide->category)
            ->latest('published_at')
            ->take($limit)
            ->get();

        if ($sameCategoryGuides->count() >= $limit) {
            return $sameCategoryGuides;
        }

        return $sameCategoryGuides->merge(
            Guide::published()
                ->whereKeyNot($guide->getKey())
                ->whereNotIn('id', $sameCategoryGuides->modelKeys())
                ->latest('published_at')
                ->take($limit - $sameCategoryGuides->count())
                ->get()
        );
    }

    public static function previousEpisode(Episode $episode): ?Episode
    {
        if (! $episode->published_at) {
            return null;
        }

        return Episode::published()
            ->where('published_at', '<', $episode->published_at)
            ->latest('published_at')
            ->first();
    }

    public static function nextEpisode(Episode $episode): ?Episode
    {
        if (! $episode->published_at) {
            return null;
        }

        return Episode::published()
            ->where('published_at', '>', $episode->published_at)
            ->oldest('published_at')
            ->first();
    }
}
