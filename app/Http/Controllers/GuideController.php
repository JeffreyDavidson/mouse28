<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Support\ContentContinuation;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->string('category')->toString();
        if (! array_key_exists($category, Guide::CATEGORIES)) {
            $category = '';
        }

        $guides = Guide::published()
            ->when($category, fn ($query) => $query->where('category', $category))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
        $categoryLabel = Guide::CATEGORIES[$category] ?? null;
        $canonicalParameters = array_filter([
            'category' => $category ?: null,
            'page' => $guides->currentPage() > 1 ? $guides->currentPage() : null,
        ]);

        return view('guides.index', [
            'category' => $category,
            'guides' => $guides,
            'pageTitle' => $categoryLabel ? "{$categoryLabel} Guides | Mouse28" : 'Disney Parks Guides | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Practical Mouse28 {$categoryLabel} guides for planning informed Disney park visits."
                : 'Practical, regularly reviewed Disney park guides for accessibility, planning, food, and family visits.',
            'canonicalUrl' => route('guides.index', $canonicalParameters),
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
            'relatedGuides' => ContentContinuation::relatedGuides($guide),
        ]);
    }
}
