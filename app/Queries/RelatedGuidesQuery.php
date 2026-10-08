<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Guide;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads the published guides to suggest after a guide: the newest in its category, then the
 * newest of any category, with ties broken by id so the order is stable on MySQL. Selects
 * only the columns the guide cards render.
 */
final class RelatedGuidesQuery
{
    private const array CARD_COLUMNS = ['id', 'slug', 'title', 'category', 'featured_image_path'];

    /** @return Collection<int, Guide> */
    public function get(Guide $guide, int $limit = 3): Collection
    {
        $relatedGuides = Guide::published()
            ->select(self::CARD_COLUMNS)
            ->whereKeyNot($guide->getKey())
            ->where('category', $guide->category)
            ->latest('published_at')
            ->latest('id')
            ->take($limit)
            ->get();

        if ($relatedGuides->count() < $limit) {
            $latestGuides = Guide::published()
                ->select(self::CARD_COLUMNS)
                ->whereKeyNot($guide->getKey())
                ->whereNotIn('id', $relatedGuides->modelKeys())
                ->latest('published_at')
                ->latest('id')
                ->take($limit - $relatedGuides->count())
                ->get();

            $relatedGuides = $relatedGuides->merge($latestGuides);
        }

        return $relatedGuides;
    }
}
