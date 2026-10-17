<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\PodcastLinks;
use App\Support\PrimaryPodcast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

class HomeViewModel
{
    public function __construct(private readonly PrimaryPodcast $primaryPodcast) {}

    /**
     * @return array{
     *     featuredPost: Post|null,
     *     latestPosts: Collection<int, Post>,
     *     latestEpisodes: Collection<int, Episode>,
     *     latestGuides: Collection<int, Guide>,
     *     planningPosts: Collection<int, Post>,
     *     podcastLinks: list<array{label: string, url: string, description: string}>
     * }
     */
    public function data(): array
    {
        $posts = Post::published()
            ->select(['id', 'slug', 'title', 'category_id', 'featured_image_path', 'published_at'])
            ->with('category:id,name,slug')
            ->newestFirst()
            ->take(4)
            ->get();
        $featuredPost = $posts->first();
        $latestPosts = $posts->skip(1)
            ->values();
        $latestEpisodes = Episode::published()
            ->select(['id', 'slug', 'title', 'description', 'episode_number', 'duration_seconds', 'transistor_url'])
            ->newestFirst()
            ->take(3)
            ->get();
        $latestGuides = Config::boolean('mouse28.guides_enabled')
            ? Guide::published()
                ->select(['id', 'slug', 'title', 'excerpt', 'category', 'featured_image_path'])
                ->newestFirst()
                ->take(2)
                ->get()
            : new Collection;
        $planningPosts = Config::boolean('mouse28.guides_enabled') && $latestGuides->isEmpty()
            ? Post::published()
                ->select(['id', 'slug', 'title', 'category_id', 'featured_image_path'])
                ->with('category:id,name,slug')
                ->whereHas('category', fn (Builder $query) => $query->whereIn('slug', Config::array('mouse28.home_planning_category_slugs')))
                ->newestFirst()
                ->take(2)
                ->get()
            : new Collection;

        return [
            'featuredPost' => $featuredPost,
            'latestPosts' => $latestPosts,
            'latestEpisodes' => $latestEpisodes,
            'latestGuides' => $latestGuides,
            'planningPosts' => $planningPosts,
            'podcastLinks' => $this->appListenLinks(),
        ];
    }

    /**
     * The show's listening links for the homepage, or none when the show has no podcast-app
     * link yet: the RSS feed is always offered, and on its own it is too technical a prompt.
     *
     * @return list<array{label: string, url: string, description: string}>
     */
    private function appListenLinks(): array
    {
        $links = PodcastLinks::for($this->primaryPodcast->current());

        return count($links) > 1 ? $links : [];
    }
}
