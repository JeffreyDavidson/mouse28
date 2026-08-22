<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuide extends EditRecord
{
    protected static string $resource = GuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->icon('heroicon-o-eye')
                ->authorize('view')
                ->url(fn (): string => route('preview.guides', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
