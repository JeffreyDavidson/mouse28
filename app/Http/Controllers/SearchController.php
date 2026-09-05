<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): View
    {
        $query = $request->string('q')->trim()->toString();

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

        return view('search', [
            'query' => $query,
            'posts' => $posts,
            'guides' => $guides,
            'episodes' => $episodes,
            'resultCount' => $posts->count() + $guides->count() + $episodes->count(),
        ]);
    }
}
