<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Queries\SitemapQuery;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;

final readonly class SitemapViewModel
{
    public function __construct(private SitemapQuery $query) {}

    /**
     * Every public page, weekly at priority 0.8 (the home page 1.0), then each live post,
     * episode, newsletter issue and guide with its modification date. Guides are listed
     * only while `mouse28.guides_enabled` is on, and records without an `updated_at` are
     * left out.
     *
     * @return array{urls: list<array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string}>}
     */
    public function data(): array
    {
        $staticRoutes = ['home', 'blog.index', 'episodes.index', 'newsletter.index', 'about', 'contact.create', 'privacy'];
        $contentUrls = [
            ...$this->contentUrls($this->query->posts(), 'blog.show', '0.7'),
            ...$this->contentUrls($this->query->episodes(), 'episodes.show', '0.7'),
            ...$this->contentUrls($this->query->newsletterIssues(), 'newsletter.issue', '0.6'),
        ];

        if (Config::boolean('mouse28.guides_enabled')) {
            $staticRoutes[] = 'guides.index';
            $contentUrls = [
                ...$contentUrls,
                ...$this->contentUrls($this->query->guides(), 'guides.show', '0.8'),
            ];
        }

        $staticUrls = array_map(
            fn (string $routeName): array => $this->url(route($routeName), null, 'weekly', $routeName === 'home' ? '1.0' : '0.8'),
            $staticRoutes,
        );

        return ['urls' => [...$staticUrls, ...$contentUrls]];
    }

    /**
     * @param  iterable<Post|Episode|NewsletterIssue|Guide>  $records
     * @return list<array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string}>
     */
    private function contentUrls(iterable $records, string $routeName, string $priority): array
    {
        $urls = [];

        foreach ($records as $record) {
            if ($record->updated_at === null) {
                continue;
            }

            $urls[] = $this->url(route($routeName, $record), $record->updated_at, 'monthly', $priority);
        }

        return $urls;
    }

    /** @return array{loc: string, lastmod: CarbonInterface|null, changefreq: string, priority: string} */
    private function url(string $loc, ?CarbonInterface $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
