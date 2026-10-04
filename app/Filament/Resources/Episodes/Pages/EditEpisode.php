<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

/** @property Episode $record */
class EditEpisode extends EditRecord
{
    #[\Override]
    protected static string $resource = EpisodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            Action::make('preview')
                ->icon(Heroicon::OutlinedEye)
                ->authorize('view')
                ->url(fn (PreviewUrlGenerator $previewUrls): string => $previewUrls->for($this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
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
