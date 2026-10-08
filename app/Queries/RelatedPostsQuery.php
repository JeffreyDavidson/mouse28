<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads the published posts to suggest after a post: the newest in its category, then the
 * newest of any category, with ties broken by id so the order is stable on MySQL. Selects
 * only the columns the post cards render.
 */
final class RelatedPostsQuery
{
    private const array CARD_COLUMNS = ['id', 'slug', 'title', 'category_id', 'content', 'featured_image_path'];

    /** @return Collection<int, Post> */
    public function get(Post $post, int $limit = 3): Collection
    {
        if ($limit < 1) {
            return new Collection;
        }

        $relatedPosts = Post::published()
            ->select(self::CARD_COLUMNS)
            ->with('category:id,name,slug')
            ->whereKeyNot($post->getKey())
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->latest('id')
            ->take($limit)
            ->get();

        if ($relatedPosts->count() < $limit) {
            $latestPosts = Post::published()
                ->select(self::CARD_COLUMNS)
                ->with('category:id,name,slug')
                ->whereKeyNot($post->getKey())
                ->whereNotIn('id', $relatedPosts->modelKeys())
                ->latest('published_at')
                ->latest('id')
                ->take($limit - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($latestPosts);
        }

        return $relatedPosts;
    }
}
