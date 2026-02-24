<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');

        $posts = Post::published()
            ->when($category, fn($q) => $q->where('category', $category))
            ->latest('published_at')
            ->paginate(12);

        return view('blog.index', compact('posts', 'category'));
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
