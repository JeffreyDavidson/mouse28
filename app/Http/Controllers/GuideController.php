<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $query = Guide::published()->orderBy('sort_order')->orderBy('title');

        $activePark = $request->query('park');
        $activeCategory = $request->query('category');

        if ($activePark && array_key_exists($activePark, Guide::PARKS)) {
            $query->park($activePark);
        }

        if ($activeCategory && array_key_exists($activeCategory, Guide::CATEGORIES)) {
            $query->category($activeCategory);
        }

        $guides = $query->get()->groupBy('park');

        return view('guides.index', [
            'guides' => $guides,
            'activePark' => $activePark,
            'activeCategory' => $activeCategory,
            'parks' => Guide::PARKS,
            'categories' => Guide::CATEGORIES,
        ]);
    }

    public function show(Guide $guide)
    {
        $relatedGuides = Guide::published()
            ->park($guide->park)
            ->where('id', '!=', $guide->id)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return view('guides.show', [
            'guide' => $guide,
            'relatedGuides' => $relatedGuides,
            'categories' => Guide::CATEGORIES,
        ]);
    }
}
