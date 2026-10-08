<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Post;
use App\Queries\RssFeedQuery;
use App\Support\PlainText;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

final readonly class RssFeedViewModel
{
    public function __construct(private RssFeedQuery $query) {}

    /**
     * The blog feed's channel, its logo and its newest published posts. A post without
     * an excerpt is described by the start of its content in plain text.
     *
     * @return array{title: string, link: string, description: string, feedUrl: string, image: array{url: string, title: string, link: string}, items: array<int, array{title: string, link: string, description: string|null, publishedAt: CarbonInterface|null, category: string|null}>}
     */
    public function data(): array
    {
        $siteName = Config::string('seo.site_name');

        return [
            'title' => "{$siteName} Blog",
            'link' => route('blog.index'),
            'description' => Config::string('seo.feed_description'),
            'feedUrl' => route('rss.blog'),
            'image' => [
                'url' => url('/images/logo.jpg'),
                'title' => $siteName,
                'link' => route('home'),
            ],
            'items' => $this->query->get()
                ->map(fn (Post $post): array => [
                    'title' => $post->title,
                    'link' => route('blog.show', $post),
                    'description' => $post->excerpt ?: Str::limit(PlainText::fromMarkdown($post->content), 300),
                    'publishedAt' => $post->published_at,
                    'category' => $post->category === null
                        ? null
                        : $post->category_label,
                ])
                ->all(),
        ];
    }
}
