<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class ContentContinuation
{
    /** @return Collection<int, Post> */
    public static function relatedPosts(Post $post, int $limit = 5): Collection
    {
        $sameCategoryPosts = Post::published()
            ->select(['id', 'slug', 'title', 'category', 'body', 'cover_image'])
            ->whereKeyNot($post->getKey())
            ->where('category', $post->category)
            ->latest('published_at')
            ->take($limit)
            ->get();

        if ($sameCategoryPosts->count() >= $limit) {
            return $sameCategoryPosts;
        }

        return $sameCategoryPosts->merge(
            Post::published()
                ->select(['id', 'slug', 'title', 'category', 'body', 'cover_image'])
                ->whereKeyNot($post->getKey())
                ->whereNotIn('id', $sameCategoryPosts->modelKeys())
                ->latest('published_at')
                ->take($limit - $sameCategoryPosts->count())
                ->get()
        );
    }

    /** @return Collection<int, Guide> */
    public static function relatedGuides(Guide $guide, int $limit = 3): Collection
    {
        $sameCategoryGuides = Guide::published()
            ->select(['id', 'slug', 'title', 'category', 'cover_image'])
            ->whereKeyNot($guide->getKey())
            ->where('category', $guide->category)
            ->latest('published_at')
            ->take($limit)
            ->get();

        if ($sameCategoryGuides->count() >= $limit) {
            return $sameCategoryGuides;
        }

        return $sameCategoryGuides->merge(
            Guide::published()
                ->select(['id', 'slug', 'title', 'category', 'cover_image'])
                ->whereKeyNot($guide->getKey())
                ->whereNotIn('id', $sameCategoryGuides->modelKeys())
                ->latest('published_at')
                ->take($limit - $sameCategoryGuides->count())
                ->get()
        );
    }

    public static function previousEpisode(Episode $episode): ?Episode
    {
        if (! $episode->published_at) {
            return null;
        }

        return Episode::published()
            ->select(['id', 'slug', 'title'])
            ->where('published_at', '<', $episode->published_at)
            ->latest('published_at')
            ->first();
    }

    public static function nextEpisode(Episode $episode): ?Episode
    {
        if (! $episode->published_at) {
            return null;
        }

        return Episode::published()
            ->select(['id', 'slug', 'title'])
            ->where('published_at', '>', $episode->published_at)
            ->oldest('published_at')
            ->first();
    }
}
