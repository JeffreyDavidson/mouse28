<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListEpisodes extends ListRecords
{
    protected static string $resource = EpisodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
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
