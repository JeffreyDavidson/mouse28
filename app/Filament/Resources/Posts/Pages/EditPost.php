<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

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
        return view('filament.resources.posts.form-header', [
            'title' => 'Edit Post',
            'subtitle' => $this->record->title,
        ]);
    }
}
