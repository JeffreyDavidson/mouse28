<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Support\ContentContinuation;
use App\Support\PrimaryPodcast;
use Illuminate\Database\Eloquent\Collection;

class EpisodeShowViewModel
{
    public function __construct(private readonly PrimaryPodcast $primaryPodcast) {}

    /**
     * @return array{
     *     episode: Episode,
     *     podcast: Podcast,
     *     relatedPosts: Collection<int, Post>,
     *     previousEpisode: Episode|null,
     *     nextEpisode: Episode|null,
     *     isPreview?: true
     * }
     */
    public function data(Episode $episode, bool $preview = false): array
    {
        $data = [
            'episode' => $episode,
            'podcast' => $this->primaryPodcast->current(),
            'relatedPosts' => $episode->posts()
                ->published()
                ->select(['posts.id', 'posts.slug', 'posts.title', 'posts.category_id', 'posts.featured_image_path'])
                ->with('category:id,name,slug')
                ->latest('published_at')
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
