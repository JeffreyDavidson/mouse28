<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Actions\PreviewContentAction;
use App\Filament\Resources\Guides\GuideResource;
use App\Models\Guide;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use JeffreyDavidson\CreatorKit\Filament\Actions\PublishContentAction;
use JeffreyDavidson\CreatorKit\Filament\Actions\UnpublishContentAction;

/** @property Guide $record */
class EditGuide extends EditRecord
{
    #[\Override]
    protected static string $resource = GuideResource::class;

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
