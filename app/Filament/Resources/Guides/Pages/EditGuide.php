<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use App\Models\Guide;
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

/** @property Guide $record */
class EditGuide extends EditRecord
{
    #[\Override]
    protected static string $resource = GuideResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.guides.form-header', [
            'title' => 'Edit Guide',
            'subtitle' => $this->record->title,
        ]);
    }

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
                            ->title('Guide is not ready to publish')
                            ->body(implode(' · ', $issues))
                            ->persistent()
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'is_published' => true,
                        'published_at' => $this->record->published_at ?? Date::now(),
                    ]);

                    Notification::make()->success()->title('Guide published')->send();
                }),
            Action::make('unpublish')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->is_published)
                ->action(function (): void {
                    $this->record->update(['is_published' => false]);
                    Notification::make()->success()->title('Guide unpublished')->send();
                }),
            Action::make('preview')
                ->icon(Heroicon::OutlinedEye)
                ->authorize('view')
                ->url(fn (): string => route('preview.guides', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
