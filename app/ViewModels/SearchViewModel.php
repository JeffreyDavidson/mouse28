<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\TextSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchViewModel
{
    /**
     * @return array{
     *     query: string,
     *     posts: LengthAwarePaginator<int, Post>,
     *     guides: LengthAwarePaginator<int, Guide>,
     *     episodes: LengthAwarePaginator<int, Episode>,
     *     resultCount: int
     * }
     */
    public function data(string $query): array
    {
        $posts = new LengthAwarePaginator((new Post)->newCollection(), 0, 6);
        $guides = new LengthAwarePaginator((new Guide)->newCollection(), 0, 6);
        $episodes = new LengthAwarePaginator((new Episode)->newCollection(), 0, 6);

        if ($query !== '') {
            $posts = Post::published()
                ->select(['slug', 'title', 'excerpt', 'category'])
                ->tap(fn (Builder $builder) => TextSearch::constrain($builder, ['title', 'excerpt', 'body'], $query))
                ->latest('published_at')
                ->latest('id')
                ->paginate(6, pageName: 'postsPage')
                ->withQueryString()
                ->fragment('post-results-heading');

            if (config('mouse28.guides_enabled')) {
                $guides = Guide::published()
                    ->select(['slug', 'title', 'excerpt', 'category'])
                    ->tap(fn (Builder $builder) => TextSearch::constrain($builder, ['title', 'excerpt', 'body'], $query))
                    ->latest('published_at')
                    ->latest('id')
                    ->paginate(6, pageName: 'guidesPage')
                    ->withQueryString()
                    ->fragment('guide-results-heading');
            }

            $episodes = Episode::published()
                ->select(['slug', 'episode_number', 'title', 'description'])
                ->tap(fn (Builder $builder) => TextSearch::constrain($builder, ['title', 'description', 'show_notes', 'transcript'], $query))
                ->latest('published_at')
                ->latest('id')
                ->paginate(6, pageName: 'episodesPage')
                ->withQueryString()
                ->fragment('episode-results-heading');
        }

        return [
            'query' => $query,
            'posts' => $posts,
            'guides' => $guides,
            'episodes' => $episodes,
            'resultCount' => $posts->total() + $guides->total() + $episodes->total(),
        ];
    }
}
