<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchViewModel
{
    /**
     * @return array{
     *     query: string,
     *     posts: Collection<int, Post>,
     *     guides: Collection<int, Guide>,
     *     episodes: Collection<int, Episode>,
     *     resultCount: int
     * }
     */
    public function data(string $query): array
    {
        $posts = collect();
        $guides = collect();
        $episodes = collect();

        if ($query !== '') {
            $posts = Post::published()
                ->where(function (Builder $builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('body', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->take(6)
                ->get();

            if (config('mouse28.guides_enabled')) {
                $guides = Guide::published()
                    ->where(function (Builder $builder) use ($query): void {
                        $builder->where('title', 'like', "%{$query}%")
                            ->orWhere('excerpt', 'like', "%{$query}%")
                            ->orWhere('body', 'like', "%{$query}%");
                    })
                    ->latest('published_at')
                    ->take(6)
                    ->get();
            }

            $episodes = Episode::published()
                ->where(function (Builder $builder) use ($query): void {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('show_notes', 'like', "%{$query}%")
                        ->orWhere('transcript', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->take(6)
                ->get();
        }

        return [
            'query' => $query,
            'posts' => $posts,
            'guides' => $guides,
            'episodes' => $episodes,
            'resultCount' => $posts->count() + $guides->count() + $episodes->count(),
        ];
    }
}
