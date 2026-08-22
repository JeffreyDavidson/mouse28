<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Support\ContentContinuation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewGuideController extends Controller
{
    public function __invoke(Guide $guide): View
    {
        Gate::authorize('view', $guide);

        return view('guides.show', [
            'guide' => $guide,
            'relatedGuides' => ContentContinuation::relatedGuides($guide),
            'isPreview' => true,
        ]);
    }
}
