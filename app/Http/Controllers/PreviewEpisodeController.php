<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeViewModel;
use Illuminate\View\View;

class PreviewEpisodeController
{
    public function __invoke(Episode $episode, EpisodeViewModel $viewModel): View
    {
        return view('pages.episodes.show', $viewModel->data($episode, preview: true));
    }
}
