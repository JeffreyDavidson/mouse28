<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $publishedPosts = Post::where('is_published', true)->count();
        $draftPosts = Post::where('is_published', false)->count();
        $publishedEpisodes = Episode::where('is_published', true)->count();

        return [
            Stat::make('Blog Posts', $publishedPosts)
                ->icon('heroicon-o-document-text')
                ->description($draftPosts > 0 ? "{$draftPosts} drafts" : 'All published')
                ->color('primary'),

            Stat::make('Episodes', $publishedEpisodes)
                ->icon('heroicon-o-microphone')
                ->description('Published')
                ->color('success'),
        ];
    }
}
