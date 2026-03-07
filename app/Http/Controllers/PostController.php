<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');

        $search = $request->query('q');
        $sort = $request->query('sort', 'newest');
        $hasAnyPosts = Post::published()->exists();
        $usedCategories = Post::published()->distinct()->pluck('category')->filter()->toArray();
        $categoryCounts = Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');

        $posts = Post::published()
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
            ))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12);

        return view('blog.index', compact('posts', 'category', 'sort', 'hasAnyPosts', 'usedCategories', 'categoryCounts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        // Prioritize same-category posts, then fill with others
        $sameCategoryPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('published_at')
            ->take(5)
            ->get();

        $recentPosts = $sameCategoryPosts->count() >= 5
            ? $sameCategoryPosts
            : $sameCategoryPosts->merge(
                Post::published()
                    ->where('id', '!=', $post->id)
                    ->whereNotIn('id', $sameCategoryPosts->pluck('id'))
                    ->latest('published_at')
                    ->take(5 - $sameCategoryPosts->count())
                    ->get()
            );
        $categoryCounts = Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');

        return view('blog.show', [
            'post' => $post->load('episode'),
            'recentPosts' => $recentPosts,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
