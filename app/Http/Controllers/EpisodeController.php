<?php

namespace App\Http\Controllers;

use App\Models\Episode;

class EpisodeController extends Controller
{
    public function index()
    {
        return view('episodes.index', [
            'episodes' => Episode::published()->latest('published_at')->paginate(12),
        ]);
    }

    public function show(Episode $episode)
    {
        abort_unless($episode->is_published, 404);

        return view('episodes.show', [
            'episode' => $episode,
            'relatedPosts' => $episode->posts()->published()->latest('published_at')->take(4)->get(),
        ]);
    }
}
