<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\ContentContinuation;

class EpisodeController extends Controller
{
    public function index()
    {
        $episodes = Episode::published()->latest('published_at')->paginate(12);

        return view('episodes.index', [
            'episodes' => $episodes,
            'podcast' => Podcast::info(),
            'canonicalUrl' => route('episodes.index', array_filter([
                'page' => $episodes->currentPage() > 1 ? $episodes->currentPage() : null,
            ])),
        ]);
    }

    public function show(Episode $episode)
    {
        abort_unless($episode->is_published && $episode->published_at?->isPast(), 404);

        return view('episodes.show', [
            'episode' => $episode,
            'podcast' => Podcast::info(),
            'relatedPosts' => $episode->posts()->published()->latest('published_at')->take(4)->get(),
            'previousEpisode' => ContentContinuation::previousEpisode($episode),
            'nextEpisode' => ContentContinuation::nextEpisode($episode),
        ]);
    }
}
