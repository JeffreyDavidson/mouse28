<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\ViewModels\GuideIndexViewModel;
use App\ViewModels\GuideViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuideController
{
    public function index(Request $request, GuideIndexViewModel $viewModel): View
    {
        abort_unless(config('mouse28.guides_enabled'), 404);

        return view('guides.index', $viewModel->data($request));
    }

    public function show(Guide $guide, GuideViewModel $viewModel): View
    {
        abort_unless(config('mouse28.guides_enabled'), 404);

        abort_unless(
            $guide->is_published && $guide->published_at?->isPast(),
            404,
        );

        return view('guides.show', $viewModel->data($guide));
    }
}
