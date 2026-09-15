<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
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
                ->where(function (Builder $builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('body', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->latest('id')
                ->paginate(6, pageName: 'postsPage')
                ->withQueryString()
                ->fragment('post-results-heading');

            if (config('mouse28.guides_enabled')) {
                $guides = Guide::published()
                    ->select(['slug', 'title', 'excerpt', 'category'])
                    ->where(function (Builder $builder) use ($query): void {
                        $builder->where('title', 'like', "%{$query}%")
                            ->orWhere('excerpt', 'like', "%{$query}%")
                            ->orWhere('body', 'like', "%{$query}%");
                    })
                    ->latest('published_at')
                    ->latest('id')
                    ->paginate(6, pageName: 'guidesPage')
                    ->withQueryString()
                    ->fragment('guide-results-heading');
            }

            $episodes = Episode::published()
                ->select(['slug', 'episode_number', 'title', 'description'])
                ->where(function (Builder $builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('show_notes', 'like', "%{$query}%")
                        ->orWhere('transcript', 'like', "%{$query}%");
                })
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
