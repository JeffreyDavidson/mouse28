<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\ContentContinuation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewEpisodeController extends Controller
{
    public function __invoke(Episode $episode): View
    {
        Gate::authorize('view', $episode);

        return view('episodes.show', [
            'episode' => $episode,
            'podcast' => Podcast::info(),
            'relatedPosts' => $episode->posts()->published()->latest('published_at')->take(4)->get(),
            'previousEpisode' => ContentContinuation::previousEpisode($episode),
            'nextEpisode' => ContentContinuation::nextEpisode($episode),
            'isPreview' => true,
        ]);
    }
}
