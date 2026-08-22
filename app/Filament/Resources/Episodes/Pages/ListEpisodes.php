<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListEpisodes extends ListRecords
{
    protected static string $resource = EpisodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs attention')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('episodes.id', Episode::query()->needsAttention()->select('id'))),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('episodes.id', Episode::query()->drafts()->select('id'))),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('episodes.id', Episode::query()->scheduled()->select('id'))),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('episodes.id', Episode::query()->published()->select('id'))),
        ];
    }

    public function getHeader(): ?View
    {
        $total = Episode::count();
        $published = Episode::where('is_published', true)->count();
        $drafts = Episode::where('is_published', false)->count();

        return view('filament.resources.episodes.header', [
            'total' => $total,
            'published' => $published,
            'drafts' => $drafts,
            'createUrl' => EpisodeResource::getUrl('create'),
        ]);
    }
}
