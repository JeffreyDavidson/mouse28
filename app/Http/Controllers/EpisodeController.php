<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\ViewModels\EpisodeIndexViewModel;
use App\ViewModels\EpisodeViewModel;
use Illuminate\View\View;

class EpisodeController
{
    public function index(EpisodeIndexViewModel $viewModel): View
    {
        return view('episodes.index', $viewModel->data());
    }

    public function show(Episode $episode, EpisodeViewModel $viewModel): View
    {
        abort_unless($episode->is_published && $episode->published_at?->isPast(), 404);

        return view('episodes.show', $viewModel->data($episode));
    }
}
