<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Actions\GenerateResponsiveCover;
use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use RuntimeException;

/** @property Post $record */
class EditPost extends EditRecord
{
    #[\Override]
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateArtwork')
                ->label('Generate responsive artwork')
                ->icon(Heroicon::OutlinedPhoto)
                ->authorize('update')
                ->requiresConfirmation()
                ->modalDescription('Generate missing responsive copies of this saved cover. The original image is preserved.')
                ->visible(fn (): bool => $this->record->isLive() && filled($this->record->cover_image))
                ->action(function (GenerateResponsiveCover $generateCover): void {
                    $notification = Notification::make();
                    try {
                        $generateCover($this->record->refresh());
                        $notification->success()->title('Responsive artwork prepared');
                    } catch (RuntimeException) {
                        $notification->danger()->title('Artwork generation failed')->body('The original cover is unchanged. Check its format and image-driver support.');
                    }
                    $notification->send();
                }),
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            Action::make('preview')
                ->icon(Heroicon::OutlinedEye)
                ->authorize('view')
                ->url(fn (): string => route('preview.posts', $this->record))
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
