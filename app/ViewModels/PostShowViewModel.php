<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Post;
use App\Queries\RelatedPostsQuery;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PostShowViewModel
{
    public function __construct(private readonly RelatedPostsQuery $relatedPostsQuery) {}

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
            'authors',
            'category',
            'episodes' => $preview ? $this->orderedEpisodes(...) : $this->publishedEpisodes(...),
        ]);

        $data = [
            'post' => $post,
            'recentPosts' => $this->relatedPostsQuery->get($post, limit: 2),
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
