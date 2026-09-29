<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\View\View;

class PreviewEpisodeController
{
    public function __invoke(Episode $episode, EpisodeShowViewModel $viewModel): View
    {
        return view('pages.episodes.show', $viewModel->data($episode, preview: true));
    }
}
