<?php

namespace App\Filament\Widgets;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
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

    /** @return list<array{label: string, value: int, icon: Heroicon, description: string, textClass: string}> */
    public function getStats(): array
    {
        $publishedPosts = Post::published()->count();
        $publishedEpisodes = Episode::published()->count();
        $publishedGuides = Guide::published()->count();
        $guidesDueForReview = Guide::published()
            ->reviewDue()
            ->count();
        $postsDueForReview = Post::published()
            ->reviewDue()
            ->count();
        $drafts = Post::query()
            ->whereIn('status', [PublishStatus::Draft, PublishStatus::InReview])
            ->count()
            + Episode::query()
                ->whereIn('status', [PublishStatus::Draft, PublishStatus::InReview])
                ->count()
            + Guide::query()
                ->whereIn('status', [PublishStatus::Draft, PublishStatus::InReview])
                ->count();

        return [
            [
                'label' => ContentType::Post->pluralLabel(),
                'value' => $publishedPosts,
                'icon' => ContentType::Post->getIcon(),
                'description' => $postsDueForReview > 0 ? "{$postsDueForReview} need review" : 'Reviews current',
                'textClass' => ContentType::Post->textClass(),
            ],
            [
                'label' => ContentType::Episode->pluralLabel(),
                'value' => $publishedEpisodes,
                'icon' => ContentType::Episode->getIcon(),
                'description' => 'Published',
                'textClass' => ContentType::Episode->textClass(),
            ],
            [
                'label' => ContentType::Guide->pluralLabel(),
                'value' => $publishedGuides,
                'icon' => ContentType::Guide->getIcon(),
                'description' => $guidesDueForReview > 0 ? "{$guidesDueForReview} need review" : 'Reviews current',
                'textClass' => ContentType::Guide->textClass(),
            ],
            [
                'label' => 'Drafts',
                'value' => $drafts,
                'icon' => Heroicon::OutlinedPencilSquare,
                'description' => 'All content',
                'textClass' => 'text-mouse-gold-dark',
            ],
            [
                'label' => 'Subscribers',
                'value' => Subscriber::active()->count(),
                'icon' => Heroicon::OutlinedUsers,
                'description' => 'Active newsletter subscribers',
                'textClass' => 'text-mouse-purple-light',
            ],
        ];
    }
}
