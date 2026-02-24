<?php

namespace App\Http\Controllers;

use App\Models\CommunityStory;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        $featuredPost = Post::published()->latest('published_at')->first();
        $latestPosts = Post::published()->latest('published_at')
            ->when($featuredPost, fn($q) => $q->where('id', '!=', $featuredPost->id))
            ->take(6)->get();
        $latestEpisodes = Episode::published()->latest('published_at')->take(3)->get();
        $popularGuides = Guide::published()->latest()->take(4)->get();
        $communityStories = CommunityStory::approved()->featured()->latest()->take(2)->get();

        // Trust signal counts
        $guidesCount = Guide::published()->count();
        $storiesCount = CommunityStory::approved()->count();

        return view('home', compact(
            'featuredPost',
            'latestPosts',
            'latestEpisodes',
            'popularGuides',
            'communityStories',
            'guidesCount',
            'storiesCount',
        ));
    }
}
