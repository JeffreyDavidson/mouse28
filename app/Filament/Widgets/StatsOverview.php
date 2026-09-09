<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\ResendAudience;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class StatsOverview extends Widget
{
    #[\Override]
    protected static ?int $sort = -2;

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected string $view = 'filament.widgets.stats-overview';

    /** @return list<array{label: string, value: int, icon: Heroicon, description: string, color: string}> */
    public function getStats(): array
    {
        $publishedPosts = Post::published()->count();
        $publishedEpisodes = Episode::published()->count();
        $publishedGuides = Guide::published()->count();
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
                'icon' => Heroicon::OutlinedBookOpen,
                'description' => $guidesDueForReview > 0 ? "{$guidesDueForReview} need review" : 'Reviews current',
                'color' => '#4a90a4',
            ],
            [
                'label' => 'Blog Posts',
                'value' => $publishedPosts,
                'icon' => Heroicon::OutlinedDocumentText,
                'description' => $postsDueForReview > 0 ? "{$postsDueForReview} need review" : 'Reviews current',
                'color' => '#5b3e9e',
            ],
            [
                'label' => 'Episodes',
                'value' => $publishedEpisodes,
                'icon' => Heroicon::OutlinedMicrophone,
                'description' => 'Published',
                'color' => '#d4a843',
            ],
            [
                'label' => 'Drafts',
                'value' => $drafts,
                'icon' => Heroicon::OutlinedPencilSquare,
                'description' => 'All content',
                'color' => '#e8a838',
            ],
            [
                'label' => 'Subscribers',
                'value' => count($audience['subscribers']),
                'icon' => Heroicon::OutlinedUsers,
                'description' => $audience['error'] ? 'Unavailable' : 'Newsletter',
                'color' => '#7b5eb5',
            ],
        ];
    }
}
