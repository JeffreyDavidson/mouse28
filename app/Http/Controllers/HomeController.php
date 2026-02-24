<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Post;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'latestEpisodes' => Episode::published()->latest('published_at')->take(3)->get(),
            'latestPosts' => Post::published()->latest('published_at')->take(3)->get(),
        ]);
    }
}
