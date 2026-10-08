<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Presenters\EpisodePresenter;
use App\Presenters\PodcastPresenter;
use App\Support\ContentContinuation;
use App\Support\PodcastLinks;
use App\Support\PrimaryPodcast;
use Illuminate\Database\Eloquent\Collection;

class EpisodeShowViewModel
{
    public function __construct(private readonly PrimaryPodcast $primaryPodcast) {}

    /**
     * @return array{
     *     episode: Episode,
     *     podcast: Podcast,
     *     embedUrl: string|null,
     *     duration: string,
     *     isSparseEpisode: bool,
     *     coverImage: string,
     *     coverSrcset: string|null,
     *     listenLinks: list<array{label: string, url: string, description: string}>,
     *     relatedPosts: Collection<int, Post>,
     *     previousEpisode: Episode|null,
     *     nextEpisode: Episode|null,
     *     isPreview?: true
     * }
     */
    public function data(Episode $episode, bool $preview = false): array
    {
        $podcast = $this->primaryPodcast->current();
        $presenter = EpisodePresenter::from($episode);

        $data = [
            'episode' => $episode,
            'podcast' => $podcast,
            'embedUrl' => $episode->transistorEmbedUrl(),
            'duration' => $presenter->duration(),
            'isSparseEpisode' => $presenter->isSparse(),
            'coverImage' => $presenter->coverImageUrl(PodcastPresenter::from($podcast)),
            'coverSrcset' => $presenter->coverSrcset(),
            'listenLinks' => PodcastLinks::for($podcast, $episode),
            'relatedPosts' => $episode->posts()
                ->published()
                ->select(['posts.id', 'posts.slug', 'posts.title', 'posts.category_id', 'posts.featured_image_path'])
                ->with('category:id,name,slug')
                ->latest('published_at')
                ->latest('posts.id')
                ->take(4)
                ->get(),
            'previousEpisode' => ContentContinuation::previousEpisode($episode),
            'nextEpisode' => ContentContinuation::nextEpisode($episode),
        ];

        if ($preview) {
            $data['isPreview'] = true;
        }

        return $data;
    }
}
