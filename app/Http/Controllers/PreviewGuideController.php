<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewGuideController extends Controller
{
    public function __invoke(Guide $guide): View
    {
        Gate::authorize('view', $guide);

        return view('guides.show', [
            'guide' => $guide,
            'relatedGuides' => Guide::published()
                ->whereKeyNot($guide->getKey())
                ->where('category', $guide->category)
                ->latest('published_at')
                ->take(3)
                ->get(),
            'isPreview' => true,
        ]);
    }
}
