<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Enums\ContentType;
use App\Filament\Actions\PreviewContentAction;
use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Guides\GuideResource;
use App\Models\Guide;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

/** @property Guide $record */
class EditGuide extends EditRecord
{
    #[\Override]
    protected static string $resource = GuideResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.content.form-header', [
            'type' => ContentType::Guide,
            'title' => 'Edit Guide',
            'subtitle' => $this->record->title,
        ]);
    }

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
}
