<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\NewsletterIssue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

final class NewsletterRssFeedQuery
{
    /**
     * The newest published newsletter issues, newest first. The limit is
     * `mouse28.newsletter_feed_items`.
     *
     * @return Collection<int, NewsletterIssue>
     */
    public function get(): Collection
    {
        return NewsletterIssue::query()
            ->published()
            ->latest('published_at')
            ->latest('id')
            ->take(Config::integer('mouse28.newsletter_feed_items'))
            ->get();
    }
}
