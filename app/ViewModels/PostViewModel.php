<?php

namespace App\ViewModels;

use App\Models\Post;
use App\Support\ContentContinuation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostViewModel
{
    /**
     * @return array{
     *     post: Post,
     *     recentPosts: EloquentCollection<int, Post>,
     *     isPreview?: true
     * }
     */
    public function data(Post $post, bool $preview = false): array
    {
        $post->load($preview ? 'episode' : [
            'episode' => fn (BelongsTo $query) => $query
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
        ]);

        $data = [
            'post' => $post,
            'recentPosts' => ContentContinuation::relatedPosts($post),
        ];

        if ($preview) {
            $data['isPreview'] = true;
        }

        return $data;
    }
}
