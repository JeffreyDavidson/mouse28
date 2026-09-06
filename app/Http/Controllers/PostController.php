<?php

namespace App\Http\Controllers;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Support\ContentContinuation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController
{
    public function index(Request $request): View
    {
        $categoryEnum = PostCategory::tryFrom($request->string('category')->toString());
        $category = $categoryEnum->value ?? '';

        $search = $request->string('q')->trim()->limit(100)->toString();
        $sort = $request->string('sort', 'newest')->toString();
        if (! in_array($sort, ['newest', 'oldest'], true)) {
            $sort = 'newest';
        }
        $hasAnyPosts = Post::published()->exists();
        $usedCategories = Post::published()->distinct()->pluck('category')->filter()
            ->map(fn (PostCategory $category): string => $category->value)
            ->all();
        $categoryCounts = Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');

        $posts = Post::published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")
            ))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12);

        $canonicalParameters = array_filter([
            'category' => $category ?: null,
            'page' => $posts->currentPage() > 1 ? $posts->currentPage() : null,
        ]);
        $categoryLabel = $categoryEnum?->getLabel();

        return view('blog.index', [
            'posts' => $posts,
            'category' => $category,
            'sort' => $sort,
            'hasAnyPosts' => $hasAnyPosts,
            'usedCategories' => $usedCategories,
            'categoryCounts' => $categoryCounts,
            'pageTitle' => $categoryLabel ? "{$categoryLabel} | Mouse28" : 'Disney Parks Blog | Mouse28',
            'pageDescription' => $categoryLabel
                ? "Mouse28 {$categoryLabel} articles, family experiences, and practical Disney park takeaways."
                : 'Disney park accessibility tips, trip reports, family experiences, news, and practical planning from Jeffrey and Cassie Davidson.',
            'canonicalUrl' => route('blog.index', $canonicalParameters),
            'robots' => $search !== '' || $sort !== 'newest' ? 'noindex,follow' : 'index,follow',
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);
        $categoryCounts = Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');

        return view('blog.show', [
            'post' => $post->load([
                'episode' => fn (BelongsTo $query) => $query
                    ->where('is_published', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now()),
            ]),
            'recentPosts' => ContentContinuation::relatedPosts($post),
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
