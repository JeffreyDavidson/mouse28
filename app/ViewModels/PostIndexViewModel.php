<?php

namespace App\ViewModels;

use App\Enums\PostCategory;
use Illuminate\Http\Request;

class PostIndexViewModel
{
    /**
     * @return array{
     *     category: string,
     *     search: string,
     *     sort: string,
     *     pageTitle: string,
     *     pageDescription: string,
     *     canonicalUrl: string,
     *     robots: string
     * }
     */
    public function data(Request $request): array
    {
        $categoryEnum = PostCategory::tryFrom($request->string('category')->toString());
        $category = $categoryEnum->value ?? '';
        $search = $request->string('q')->trim()->limit(100, '')->toString();
        $sort = $request->string('sort', 'newest')->toString();

        if (! in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }

        $page = max($request->integer('page', 1), 1);
        $categoryLabel = $categoryEnum?->getLabel();

        return [
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'pageTitle' => $categoryLabel ? "{$categoryLabel} | Mouse28" : 'Disney Parks Blog | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Mouse28 {$categoryLabel} articles, family experiences, and practical Disney park takeaways."
                : 'Disney park accessibility tips, trip reports, family experiences, news, and practical planning from Jeffrey and Cassie Davidson.',
            'canonicalUrl' => route('blog.index', array_filter([
                'category' => $category ?: null,
                'page' => $page > 1 ? $page : null,
            ])),
            'robots' => $search !== '' || $sort !== 'newest' ? 'noindex,follow' : 'index,follow',
        ];
    }
}
