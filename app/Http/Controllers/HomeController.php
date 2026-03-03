<?php

namespace App\Http\Controllers;

use App\Models\Episode;
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

        return view('home', compact(
            'featuredPost',
            'latestPosts',
            'latestEpisodes',
        ));
    }
}
