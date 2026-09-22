<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeIndexViewModel;
use App\ViewModels\EpisodeViewModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EpisodeController
{
    public function index(EpisodeIndexViewModel $viewModel): View
    {
        return view('pages.episodes.index', $viewModel->data());
    }

    public function show(Episode $episode, EpisodeViewModel $viewModel): View
    {
        Gate::authorize('viewPublic', $episode);

        return view('pages.episodes.show', $viewModel->data($episode));
    }
}
