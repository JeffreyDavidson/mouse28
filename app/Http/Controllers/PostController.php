<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\PostIndexViewModel;
use App\ViewModels\PostViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController
{
    public function index(Request $request, PostIndexViewModel $viewModel): View
    {
        return view('blog.index', $viewModel->data($request));
    }

    public function show(Post $post, PostViewModel $viewModel): View
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        return view('blog.show', $viewModel->data($post));
    }
}
