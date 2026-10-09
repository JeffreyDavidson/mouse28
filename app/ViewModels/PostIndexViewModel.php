<?php

namespace App\ViewModels;

use App\Data\BlogFilters;
use App\Enums\BlogSort;
use Illuminate\Http\Request;

class PostIndexViewModel
{
    /** @return array{pageTitle: string, pageDescription: string, canonicalUrl: string, robots: string} */
    public function data(Request $request): array
    {
        $filters = BlogFilters::fromInput(
            $request->string('category')
                ->toString(),
            $request->string('q')
                ->toString(),
            $request->string('sort')
                ->toString(),
        );

        return $this->metadata($filters, max($request->integer('page', 1), 1));
    }

    /** @return array{pageTitle: string, pageDescription: string, canonicalUrl: string, robots: string} */
    public function metadata(BlogFilters $filters, int $page): array
    {
        $categoryLabel = $filters->category?->name;

        return [
            'pageTitle' => $categoryLabel ? "{$categoryLabel} | Mouse28" : 'Disney Parks Blog | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Mouse28 {$categoryLabel} articles, family experiences, and practical Disney park takeaways."
                : 'Disney park accessibility tips, trip reports, family experiences, news, and practical planning from Jeffrey and Cassie Davidson.',
            'canonicalUrl' => route('blog.index', array_filter([
                'category' => $filters->category?->slug,
                'page' => $page > 1 ? $page : null,
            ])),
            'robots' => $filters->search !== '' || $filters->sort !== BlogSort::Newest ? 'noindex,follow' : 'index,follow',
        ];
    }
}
