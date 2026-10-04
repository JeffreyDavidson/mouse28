<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

class HomeViewModel
{
    /**
     * @return array{
     *     featuredPost: Post|null,
     *     latestPosts: Collection<int, Post>,
     *     latestEpisodes: Collection<int, Episode>,
     *     latestGuides: Collection<int, Guide>,
     *     planningPosts: Collection<int, Post>
     * }
     */
    public function data(): array
    {
        $posts = Post::published()
            ->select(['id', 'slug', 'title', 'category_id', 'featured_image_path', 'published_at'])
            ->with('category:id,name,slug')
            ->latest('published_at')
            ->take(4)
            ->get();
        $featuredPost = $posts->first();
        $latestPosts = $posts->skip(1)->values();
        $latestEpisodes = Episode::published()
            ->select(['id', 'slug', 'title', 'description', 'episode_number', 'duration_seconds'])
            ->latest('published_at')
            ->take(3)
            ->get();
        $latestGuides = Config::boolean('mouse28.guides_enabled')
            ? Guide::published()
                ->select(['id', 'slug', 'title', 'excerpt', 'category', 'featured_image_path'])
                ->latest('published_at')
                ->take(2)
                ->get()
            : new Collection;
        $planningPosts = Config::boolean('mouse28.guides_enabled') && $latestGuides->isEmpty()
            ? Post::published()
                ->select(['id', 'slug', 'title', 'category_id', 'featured_image_path'])
                ->with('category:id,name,slug')
                ->whereHas('category', fn (Builder $query) => $query->whereIn('slug', ['park-accessibility', 'disney-tips', 'autism-awareness']))
                ->latest('published_at')
                ->take(2)
                ->get()
            : new Collection;

        return [
            'featuredPost' => $featuredPost,
            'latestPosts' => $latestPosts,
            'latestEpisodes' => $latestEpisodes,
            'latestGuides' => $latestGuides,
            'planningPosts' => $planningPosts,
        ];
    }
}
