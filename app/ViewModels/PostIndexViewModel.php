<?php

namespace App\ViewModels;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PostIndexViewModel
{
    /**
     * @return array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     category: string,
     *     sort: string,
     *     hasAnyPosts: bool,
     *     usedCategories: list<string>,
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
        $search = $request->string('q')->trim()->limit(100)->toString();
        $sort = $request->string('sort', 'newest')->toString();

        if (! in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }

        $posts = Post::published()
            ->when($category, fn (Builder $query) => $query->where('category', $category))
            ->when($search, fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12);
        $categoryLabel = $categoryEnum?->getLabel();

        return [
            'posts' => $posts,
            'category' => $category,
            'sort' => $sort,
            'hasAnyPosts' => Post::published()->exists(),
            'usedCategories' => Post::published()->distinct()->pluck('category')->filter()
                ->values()
                ->map(fn (PostCategory $category): string => $category->value)
                ->all(),
            'pageTitle' => $categoryLabel ? "{$categoryLabel} | Mouse28" : 'Disney Parks Blog | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Mouse28 {$categoryLabel} articles, family experiences, and practical Disney park takeaways."
                : 'Disney park accessibility tips, trip reports, family experiences, news, and practical planning from Jeffrey and Cassie Davidson.',
            'canonicalUrl' => route('blog.index', array_filter([
                'category' => $category ?: null,
                'page' => $posts->currentPage() > 1 ? $posts->currentPage() : null,
            ])),
            'robots' => $search !== '' || $sort !== 'newest' ? 'noindex,follow' : 'index,follow',
        ];
    }
}
