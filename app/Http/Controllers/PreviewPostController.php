<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\ViewModels\PostViewModel;
use Illuminate\View\View;

class PreviewPostController
{
    public function __invoke(Post $post, PostViewModel $viewModel): View
    {
        return view('pages.blog.show', $viewModel->data($post, preview: true));
    }
}
