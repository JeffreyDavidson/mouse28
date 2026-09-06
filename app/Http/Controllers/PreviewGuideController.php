<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\ViewModels\GuideViewModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewGuideController
{
    public function __invoke(Guide $guide, GuideViewModel $viewModel): View
    {
        Gate::authorize('view', $guide);

        return view('guides.show', $viewModel->data($guide, preview: true));
    }
}
