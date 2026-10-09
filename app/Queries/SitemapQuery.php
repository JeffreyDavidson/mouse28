<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reads the public content the sitemap lists, newest first with ties broken by id so
 * the order is stable on MySQL, selecting only the columns its URLs and modification
 * dates need.
 */
final class SitemapQuery
{
    /** @return Collection<int, Post> */
    public function posts(): Collection
    {
        return Post::published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->latest('id')
            ->get();
    }

    /** @return Collection<int, Episode> */
    public function episodes(): Collection
    {
        return Episode::published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->latest('id')
            ->get();
    }

    /** @return Collection<int, NewsletterIssue> */
    public function newsletterIssues(): Collection
    {
        return NewsletterIssue::published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->latest('id')
            ->get();
    }

    /** @return Collection<int, Guide> */
    public function guides(): Collection
    {
        return Guide::published()
            ->select(['slug', 'updated_at'])
            ->latest('published_at')
            ->latest('id')
            ->get();
    }
}
