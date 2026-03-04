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

        $posts = Post::published()
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($search, fn($q) => $q->where(fn($q) =>
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
            ))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12);

        return view('blog.index', compact('posts', 'category', 'sort', 'hasAnyPosts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        $recentPosts = Post::published()->where('id', '!=', $post->id)->latest('published_at')->take(5)->get();
        $categoryCounts = Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');

        return view('blog.show', [
            'post' => $post->load('episode'),
            'recentPosts' => $recentPosts,
            'categoryCounts' => $categoryCounts,
        ]);
    }
}
