<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\ResendAudience;
use Filament\Widgets\Widget;

class StatsOverview extends Widget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.stats-overview';

    public function getStats(): array
    {
        $publishedPosts = Post::where('is_published', true)->count();
        $publishedEpisodes = Episode::where('is_published', true)->count();
        $publishedGuides = Guide::where('is_published', true)->count();
        $guidesDueForReview = Guide::published()->reviewDue()->count();
        $postsDueForReview = Post::published()->reviewDue()->count();
        $drafts = Post::where('is_published', false)->count()
            + Episode::where('is_published', false)->count()
            + Guide::where('is_published', false)->count();
        $audience = app(ResendAudience::class)->get();

        return [
            [
                'label' => 'Guides',
                'value' => $publishedGuides,
                'icon' => 'heroicon-o-book-open',
                'description' => $guidesDueForReview > 0 ? "{$guidesDueForReview} need review" : 'Reviews current',
                'color' => '#4a90a4',
            ],
            [
                'label' => 'Blog Posts',
                'value' => $publishedPosts,
                'icon' => 'heroicon-o-document-text',
                'description' => $postsDueForReview > 0 ? "{$postsDueForReview} need review" : 'Reviews current',
                'color' => '#5b3e9e',
            ],
            [
                'label' => 'Episodes',
                'value' => $publishedEpisodes,
                'icon' => 'heroicon-o-microphone',
                'description' => 'Published',
                'color' => '#d4a843',
            ],
            [
                'label' => 'Drafts',
                'value' => $drafts,
                'icon' => 'heroicon-o-pencil-square',
                'description' => 'All content',
                'color' => '#e8a838',
            ],
            [
                'label' => 'Subscribers',
                'value' => count($audience['subscribers']),
                'icon' => 'heroicon-o-users',
                'description' => $audience['error'] ? 'Unavailable' : 'Newsletter',
                'color' => '#7b5eb5',
            ],
        ];
    }
}
