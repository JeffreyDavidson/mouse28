<?php

namespace App\Filament\Resources\Episodes\Pages;

use App\Actions\GenerateResponsiveCover;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use App\Support\EditorialReadiness;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;
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
                ->visible(fn (): bool => $this->record->is_published && ($this->record->published_at?->isPast() ?? false) && filled($this->record->cover_image))
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
            Action::make('publish')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->authorize('update')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! $this->record->is_published)
                ->action(function (): void {
                    $issues = EditorialReadiness::publishingIssues($this->record);

                    if ($issues !== []) {
                        Notification::make()
                            ->danger()
                            ->title('Episode is not ready to publish')
                            ->body(implode(' · ', $issues))
                            ->persistent()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'is_published' => true,
                        'published_at' => $this->record->published_at ?? Date::now(),
                    ]);

                    Notification::make()->success()->title('Episode published')->send();
                }),
            Action::make('unpublish')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->authorize('update')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->is_published)
                ->action(function (): void {
                    $this->record->update(['is_published' => false]);
                    Notification::make()->success()->title('Episode unpublished')->send();
                }),
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
