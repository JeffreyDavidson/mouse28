<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\PodcastLinks;
use App\Support\PrimaryPodcast;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

class EpisodeIndexViewModel
{
    public function __construct(private readonly PrimaryPodcast $primaryPodcast) {}

    /**
     * @return array{
     *     episodes: LengthAwarePaginator<int, Episode>,
     *     podcast: Podcast,
     *     podcastLinks: list<array{label: string, url: string}>,
     *     canonicalUrl: string
     * }
     */
    public function data(): array
    {
        $episodes = Episode::published()
            ->select(['id', 'slug', 'title', 'description', 'published_at', 'duration_seconds', 'season_number', 'episode_number', 'transistor_url'])
            ->latest('published_at')
            ->latest('id')
            ->paginate(Config::integer('mouse28.episodes_per_page'));

        // A page past the last one would render an empty archive with its own canonical URL.
        abort_if($episodes->currentPage() > $episodes->lastPage(), 404);

        $podcast = $this->primaryPodcast->current();

        return [
            'episodes' => $episodes,
            'podcast' => $podcast,
            'podcastLinks' => PodcastLinks::for($podcast),
            'canonicalUrl' => route('episodes.index', array_filter([
                'page' => $episodes->currentPage() > 1 ? $episodes->currentPage() : null,
            ])),
        ];
    }
}
