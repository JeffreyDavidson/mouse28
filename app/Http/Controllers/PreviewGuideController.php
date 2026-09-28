<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\ViewModels\GuideShowViewModel;
use Illuminate\View\View;

class PreviewGuideController
{
    public function __invoke(Guide $guide, GuideShowViewModel $viewModel): View
    {
        return view('pages.guides.show', $viewModel->data($guide, preview: true));
    }
}
