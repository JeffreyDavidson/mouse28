<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
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
        $drafts = Post::where('is_published', false)->count() + Episode::where('is_published', false)->count();

        return [
            [
                'label' => 'Blog Posts',
                'value' => $publishedPosts,
                'icon' => 'heroicon-o-document-text',
                'description' => 'Published',
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
                'description' => 'Posts & episodes',
                'color' => '#2d1b69',
            ],
            [
                'label' => 'Subscribers',
                'value' => 0,
                'icon' => 'heroicon-o-envelope',
                'description' => 'Coming soon',
                'color' => '#1a1040',
            ],
        ];
    }
}
