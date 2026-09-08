<?php

namespace App\ViewModels;

use App\Enums\GuideCategory;
use App\Models\Guide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GuideIndexViewModel
{
    /**
     * @return array{
     *     category: string,
     *     guides: LengthAwarePaginator<int, Guide>,
     *     pageTitle: string,
     *     pageDescription: string,
     *     canonicalUrl: string
     * }
     */
    public function data(Request $request): array
    {
        $categoryEnum = GuideCategory::tryFrom($request->string('category')->toString());
        $category = $categoryEnum->value ?? '';

        $guides = Guide::published()
            ->select(['id', 'slug', 'title', 'category', 'excerpt', 'body', 'cover_image'])
            ->when($category, fn (Builder $query): Builder => $query->where('category', $category))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();
        $categoryLabel = $categoryEnum?->getLabel();
        $canonicalParameters = array_filter([
            'category' => $category ?: null,
            'page' => $guides->currentPage() > 1 ? $guides->currentPage() : null,
        ]);

        return [
            'category' => $category,
            'guides' => $guides,
            'pageTitle' => $categoryLabel ? "{$categoryLabel} Guides | Mouse28" : 'Disney Parks Guides | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Practical Mouse28 {$categoryLabel} guides for planning informed Disney park visits."
                : 'Practical, regularly reviewed Disney park guides for accessibility, planning, food, and family visits.',
            'canonicalUrl' => route('guides.index', $canonicalParameters),
        ];
    }
}
