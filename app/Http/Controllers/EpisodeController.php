<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeIndexViewModel;
use App\ViewModels\EpisodeShowViewModel;
use Illuminate\View\View;

class EpisodeController
{
    public function index(EpisodeIndexViewModel $viewModel): View
    {
        return view('pages.episodes.index', $viewModel->data());
    }

    public function show(Episode $episode, EpisodeShowViewModel $viewModel): View
    {
        abort_unless($episode->isLive(), 404);

        return view('pages.episodes.show', $viewModel->data($episode));
    }
}
