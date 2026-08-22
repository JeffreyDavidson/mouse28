<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

/** @property Episode $record */
class EditEpisode extends EditRecord
{
    protected static string $resource = EpisodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->icon('heroicon-o-eye')
                ->authorize('view')
                ->url(fn (): string => route('preview.episodes', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.episodes.form-header', [
            'title' => 'Edit Episode',
            'subtitle' => $this->record->title,
        ]);
    }
}
