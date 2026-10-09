<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\TextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

/**
 * @phpstan-type SearchResultPage LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Guide>|LengthAwarePaginator<int, Episode>
 */
final class SearchQuery
{
    /**
     * Each group pages independently through its own query parameter (for example
     * postsPage). The page size comes from `search.per_page`. Groups are keyed by their
     * content type value, in page order, and hold the matching published models with only
     * the columns the results show. Guides are searched only while `mouse28.guides_enabled`
     * is on.
     *
     * @return array<string, SearchResultPage>
     */
    public function get(?string $query, ?SearchContentType $type = null): array
    {
        $query = trim($query ?? '');

        if ($query === '') {
            return [];
        }

        $results = [];

        foreach ($type instanceof SearchContentType ? [$type] : SearchContentType::cases() as $searchType) {
            if ($searchType === SearchContentType::Guides && ! Config::boolean('mouse28.guides_enabled')) {
                continue;
            }

            $results[$searchType->value] = $this->search($searchType, $query);
        }

        return $results;
    }

    /**
     * @return SearchResultPage
     */
    private function search(SearchContentType $type, string $query): LengthAwarePaginator
    {
        return match ($type) {
            SearchContentType::Posts => $this->paginate(
                Post::published()
                    ->select(['slug', 'title', 'excerpt', 'category_id'])
                    ->with('category:id,name')
                    ->tap(fn (Builder $postsQuery) => TextSearch::constrain($postsQuery, ['title', 'excerpt', 'content'], $query))
                    ->newestFirst(),
                'postsPage',
            ),
            SearchContentType::Guides => $this->paginate(
                Guide::published()
                    ->select(['slug', 'title', 'excerpt', 'category'])
                    ->tap(fn (Builder $guidesQuery) => TextSearch::constrain($guidesQuery, ['title', 'excerpt', 'content'], $query))
                    ->newestFirst(),
                'guidesPage',
            ),
            SearchContentType::Episodes => $this->paginate(
                Episode::published()
                    ->select(['slug', 'episode_number', 'title', 'description'])
                    ->tap(fn (Builder $episodesQuery) => TextSearch::constrain($episodesQuery, ['title', 'description', 'show_notes', 'transcript'], $query))
                    ->newestFirst(),
                'episodesPage',
            ),
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    private function paginate(Builder $query, string $pageName): LengthAwarePaginator
    {
        return $query->paginate(Config::integer('search.per_page'), pageName: $pageName);
    }
}
