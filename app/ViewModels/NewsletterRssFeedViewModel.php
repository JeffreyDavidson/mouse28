<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\NewsletterIssue;
use App\Queries\NewsletterRssFeedQuery;
use Carbon\CarbonInterface;

final readonly class NewsletterRssFeedViewModel
{
    public function __construct(private NewsletterRssFeedQuery $query) {}

    /**
     * The newsletter feed's channel and its newest published issues.
     *
     * @return array{title: string, link: string, description: string, feedUrl: string, items: array<int, array{title: string, link: string, description: string|null, publishedAt: CarbonInterface|null}>}
     */
    public function data(): array
    {
        return [
            'title' => 'Mouse28 Newsletter',
            'link' => route('newsletter.index'),
            'description' => 'Disney parks through the eyes of a family raising a daughter with autism. Notes and updates from Jeffrey and Cassie.',
            'feedUrl' => route('newsletter.rss'),
            'items' => $this->query->get()
                ->map(fn (NewsletterIssue $issue): array => [
                    'title' => $issue->title,
                    'link' => route('newsletter.issue', $issue),
                    'description' => $issue->excerpt,
                    'publishedAt' => $issue->published_at,
                ])
                ->all(),
        ];
    }
}
