<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeViewModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewEpisodeController
{
    public function __invoke(Episode $episode, EpisodeViewModel $viewModel): View
    {
        Gate::authorize('view', $episode);

        return view('episodes.show', $viewModel->data($episode, preview: true));
    }
}
