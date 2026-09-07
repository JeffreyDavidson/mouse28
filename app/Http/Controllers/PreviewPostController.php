<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\PostViewModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewPostController
{
    public function __invoke(Post $post, PostViewModel $viewModel): View
    {
        Gate::authorize('view', $post);

        return view('blog.show', $viewModel->data($post, preview: true));
    }
}
