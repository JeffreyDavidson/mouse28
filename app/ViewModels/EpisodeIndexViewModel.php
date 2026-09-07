<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\PodcastLinks;
use Illuminate\Pagination\LengthAwarePaginator;

class EpisodeIndexViewModel
{
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
        $episodes = Episode::published()->latest('published_at')->paginate(12);
        $podcast = Podcast::info();

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
