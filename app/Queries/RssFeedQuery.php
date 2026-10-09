<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

final class RssFeedQuery
{
    /**
     * The newest published posts with their category, newest first, reading only the
     * columns the feed shows. The limit is `mouse28.blog_feed_items`.
     *
     * @return Collection<int, Post>
     */
    public function get(): Collection
    {
        return Post::query()
            ->published()
            ->select(['id', 'slug', 'title', 'excerpt', 'content', 'published_at', 'category_id'])
            ->with('category:id,name')
            ->latest('published_at')
            ->latest('id')
            ->take(Config::integer('mouse28.blog_feed_items'))
            ->get();
    }
}
