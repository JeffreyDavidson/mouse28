<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use App\Models\Guide;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGuides extends ListRecords
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs attention')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->needsAttention()->select('id'))),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->drafts()->select('id'))),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->scheduled()->select('id'))),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->published()->select('id'))),
            'review-due' => Tab::make('Review due')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->published()->reviewDue()->select('id'))),
        ];
    }
}
