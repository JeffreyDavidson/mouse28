<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->string('category')->toString();
        if (! array_key_exists($category, Guide::CATEGORIES)) {
            $category = '';
        }

        return view('guides.index', [
            'category' => $category,
            'guides' => Guide::published()
                ->when($category, fn ($query) => $query->where('category', $category))
                ->latest('published_at')
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function show(Guide $guide)
    {
        abort_unless(
            $guide->is_published && $guide->published_at?->isPast(),
            404,
        );

        return view('guides.show', [
            'guide' => $guide,
            'relatedGuides' => Guide::published()
                ->whereKeyNot($guide->getKey())
                ->where('category', $guide->category)
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
