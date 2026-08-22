<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PreviewPostController extends Controller
{
    public function __invoke(Post $post): View
    {
        Gate::authorize('view', $post);

        $sameCategoryPosts = Post::published()
            ->whereKeyNot($post->getKey())
            ->where('category', $post->category)
            ->latest('published_at')
            ->take(5)
            ->get();

        $recentPosts = $sameCategoryPosts->count() >= 5
            ? $sameCategoryPosts
            : $sameCategoryPosts->merge(
                Post::published()
                    ->whereKeyNot($post->getKey())
                    ->whereNotIn('id', $sameCategoryPosts->modelKeys())
                    ->latest('published_at')
                    ->take(5 - $sameCategoryPosts->count())
                    ->get()
            );

        return view('blog.show', [
            'post' => $post->load('episode'),
            'recentPosts' => $recentPosts,
            'categoryCounts' => Post::published()->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category'),
            'isPreview' => true,
        ]);
    }
}
