<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\ContentContinuation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewPostController
{
    public function __invoke(Post $post): View
    {
        Gate::authorize('view', $post);

        return view('blog.show', [
            'post' => $post->load('episode'),
            'recentPosts' => ContentContinuation::relatedPosts($post),
            'categoryCounts' => Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category'),
            'isPreview' => true,
        ]);
    }
}
