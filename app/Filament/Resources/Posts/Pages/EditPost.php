<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentType;
use App\Filament\Actions\PreviewContentAction;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;
use JeffreyDavidson\CreatorKit\Filament\Actions\PublishContentAction;
use JeffreyDavidson\CreatorKit\Filament\Actions\UnpublishContentAction;

/** @property Post $record */
class EditPost extends EditRecord
{
    #[\Override]
    protected static string $resource = PostResource::class;

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
            'type' => ContentType::Post,
            'title' => 'Edit Post',
            'subtitle' => $this->record->title,
        ]);
    }
}
