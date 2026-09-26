<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Actions\GenerateResponsiveCover;
use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use RuntimeException;

/** @property Episode $record */
class EditEpisode extends EditRecord
{
    #[\Override]
    protected static string $resource = EpisodeResource::class;

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
                ->url(fn (): string => route('preview.episodes', $this->record))
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
