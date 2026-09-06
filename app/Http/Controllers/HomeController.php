<?php

namespace App\Http\Controllers;

use App\Enums\PostCategory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\View\View;

class HomeController
{
    public function index(): View
    {
        $featuredPost = Post::published()->latest('published_at')->first();
        $latestPosts = Post::published()->latest('published_at')
            ->when($featuredPost, fn ($q) => $q->where('id', '!=', $featuredPost->id))
            ->take(6)->get();
        $latestEpisodes = Episode::published()->latest('published_at')->take(3)->get();
        $latestGuides = config('mouse28.guides_enabled')
            ? Guide::published()->latest('published_at')->take(4)->get()
            : collect();
        $planningPosts = config('mouse28.guides_enabled') && $latestGuides->isEmpty()
            ? Post::published()
                ->whereIn('category', [PostCategory::ParkAccessibility, PostCategory::DisneyTips, PostCategory::AutismAwareness])
                ->latest('published_at')
                ->take(2)
                ->get()
            : collect();
        $podcast = Podcast::info();

        return view('home', [
            'featuredPost' => $featuredPost,
            'latestPosts' => $latestPosts,
            'latestEpisodes' => $latestEpisodes,
            'latestGuides' => $latestGuides,
            'planningPosts' => $planningPosts,
            'podcast' => $podcast,
        ]);
    }
}
