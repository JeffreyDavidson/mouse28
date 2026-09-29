<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\ViewModels\GuideIndexViewModel;
use App\ViewModels\GuideShowViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class GuideController
{
    public function index(Request $request, GuideIndexViewModel $viewModel): View
    {
        abort_if(! Config::boolean('mouse28.guides_enabled'), 404);

        return view('pages.guides.index', $viewModel->data($request));
    }

    public function show(Guide $guide, GuideShowViewModel $viewModel): View
    {
        abort_if(! Config::boolean('mouse28.guides_enabled'), 404);

        abort_unless($guide->isPublished(), 404);

        return view('pages.guides.show', $viewModel->data($guide));
    }
}
