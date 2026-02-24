<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'posts' => Post::published()->latest('published_at')->paginate(12),
        ]);
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        return view('blog.show', [
            'post' => $post->load('episode'),
        ]);
    }
}
