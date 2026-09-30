<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\Subscriber;
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

    /** @return list<array{label: string, value: int, icon: Heroicon, description: string, color: 'gold'|'purple'|'purple-light'|'teal'}> */
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

        return [
            [
                'label' => 'Blog Posts',
                'value' => $publishedPosts,
                'icon' => Heroicon::OutlinedDocumentText,
                'description' => $postsDueForReview > 0 ? "{$postsDueForReview} need review" : 'Reviews current',
                'color' => 'purple',
            ],
            [
                'label' => 'Episodes',
                'value' => $publishedEpisodes,
                'icon' => Heroicon::OutlinedMicrophone,
                'description' => 'Published',
                'color' => 'gold',
            ],
            [
                'label' => 'Guides',
                'value' => $publishedGuides,
                'icon' => Heroicon::OutlinedBookOpen,
                'description' => $guidesDueForReview > 0 ? "{$guidesDueForReview} need review" : 'Reviews current',
                'color' => 'teal',
            ],
            [
                'label' => 'Drafts',
                'value' => $drafts,
                'icon' => Heroicon::OutlinedPencilSquare,
                'description' => 'All content',
                'color' => 'gold',
            ],
            [
                'label' => 'Subscribers',
                'value' => Subscriber::active()->count(),
                'icon' => Heroicon::OutlinedUsers,
                'description' => 'Active newsletter subscribers',
                'color' => 'purple-light',
            ],
        ];
    }
}
