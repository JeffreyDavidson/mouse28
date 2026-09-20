<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\ViewModels\GuideIndexViewModel;
use App\ViewModels\GuideViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GuideController
{
    public function index(Request $request, GuideIndexViewModel $viewModel): View
    {
        abort_if(! config('mouse28.guides_enabled'), 404);

        return view('pages.guides.index', $viewModel->data($request));
    }

    public function show(Guide $guide, GuideViewModel $viewModel): View
    {
        abort_if(! config('mouse28.guides_enabled'), 404);

        Gate::authorize('viewPublic', $guide);

        return view('pages.guides.show', $viewModel->data($guide));
    }
}
