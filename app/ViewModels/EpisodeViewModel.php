<?php

namespace App\ViewModels;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Support\ContentContinuation;
use Illuminate\Database\Eloquent\Collection;

class EpisodeViewModel
{
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
            'podcast' => Podcast::info(),
            'relatedPosts' => Post::published()
                ->select(['id', 'slug', 'title', 'category', 'cover_image'])
                ->whereBelongsTo($episode)
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
