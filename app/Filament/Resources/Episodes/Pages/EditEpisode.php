<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Enums\ContentType;
use App\Filament\Actions\PreviewContentAction;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;
use JeffreyDavidson\CreatorKit\Filament\Actions\PublishContentAction;
use JeffreyDavidson\CreatorKit\Filament\Actions\UnpublishContentAction;

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
            PreviewContentAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.content.form-header', [
            'type' => ContentType::Episode,
            'title' => 'Edit Episode',
            'subtitle' => $this->record->title,
        ]);
    }
}
