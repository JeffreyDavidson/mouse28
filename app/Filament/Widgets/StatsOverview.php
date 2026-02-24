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
        $latestEpisode = Episode::latest('episode_number')->first();

        return [
            Stat::make('Total Episodes', Episode::count()),
            Stat::make('Published Episodes', Episode::where('is_published', true)->count()),
            Stat::make('Total Posts', Post::count()),
            Stat::make('Latest Episode', $latestEpisode?->title ?? 'None')
                ->description($latestEpisode ? "Ep. {$latestEpisode->episode_number}" : null),
        ];
    }
}
