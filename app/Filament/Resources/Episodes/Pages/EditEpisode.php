<?php

namespace App\Filament\Resources\Episodes\Pages;

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

/** @property Episode $record */
class EditEpisode extends EditRecord
{
    #[\Override]
    protected static string $resource = EpisodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->icon(Heroicon::OutlinedRocketLaunch)
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
                        'published_at' => $this->record->published_at ?? now(),
                    ]);

                    Notification::make()->success()->title('Episode published')->send();
                }),
            Action::make('unpublish')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
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
