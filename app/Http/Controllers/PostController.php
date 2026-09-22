<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\PostIndexViewModel;
use App\ViewModels\PostViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController
{
    public function index(Request $request, PostIndexViewModel $viewModel): View
    {
        return view('pages.blog.index', $viewModel->data($request));
    }

    public function show(Post $post, PostViewModel $viewModel): View
    {
        Gate::authorize('viewPublic', $post);

        return view('pages.blog.show', $viewModel->data($post));
    }
}
