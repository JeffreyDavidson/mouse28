<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Post;
use App\Support\ContentContinuation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostShowViewModel
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
        $post->load([
            'episodes' => $preview ? $this->orderedEpisodes(...) : $this->publishedEpisodes(...),
        ]);

        $data = [
            'post' => $post,
            'recentPosts' => ContentContinuation::relatedPosts($post, limit: 2),
        ];

        if ($preview) {
            $data['isPreview'] = true;
        }

        return $data;
    }

    /** @param BelongsToMany<Episode, Post> $query */
    private function orderedEpisodes(BelongsToMany $query): void
    {
        $query->orderBy('episode_number');
    }

    /** @param BelongsToMany<Episode, Post> $query */
    private function publishedEpisodes(BelongsToMany $query): void
    {
        $query->published();

        $this->orderedEpisodes($query);
    }
}
