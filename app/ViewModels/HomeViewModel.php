<?php

namespace App\ViewModels;

use App\Enums\PostCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class HomeViewModel
{
    /**
     * @return array{
     *     featuredPost: Post|null,
     *     latestPosts: Collection<int, Post>,
     *     latestEpisodes: Collection<int, Episode>,
     *     latestGuides: Collection<int, Guide>,
     *     planningPosts: Collection<int, Post>,
     *     podcast: Podcast
     * }
     */
    public function data(): array
    {
        $featuredPost = Post::published()->latest('published_at')->first();
        $latestPosts = Post::published()->latest('published_at')
            ->when($featuredPost, fn ($query) => $query->whereKeyNot($featuredPost->getKey()))
            ->take(6)
            ->get();
        $latestEpisodes = Episode::published()->latest('published_at')->take(3)->get();
        $latestGuides = config('mouse28.guides_enabled')
            ? Guide::published()->latest('published_at')->take(4)->get()
            : new Collection;
        $planningPosts = config('mouse28.guides_enabled') && $latestGuides->isEmpty()
            ? Post::published()
                ->whereIn('category', [PostCategory::ParkAccessibility, PostCategory::DisneyTips, PostCategory::AutismAwareness])
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
            'podcast' => Podcast::info(),
        ];
    }
}
