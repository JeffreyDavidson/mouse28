<?php

namespace App\Filament\Actions;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;

/**
 * Publishes a draft once it passes the editorial readiness checks, keeping any scheduled date.
 */
class PublishContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'publish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedRocketLaunch)
            ->authorize('update')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Post|Guide|Episode $record): bool => ! $record->is_published)
            ->action(function (Post|Guide|Episode $record): void {
                $contentType = class_basename($record);
                $issues = EditorialReadiness::publishingIssues($record);

                if ($issues !== []) {
                    Notification::make()
                        ->danger()
                        ->title("{$contentType} is not ready to publish")
                        ->body(implode(' · ', $issues))
                        ->persistent()
                        ->send();

                    return;
                }

                $record->update([
                    'is_published' => true,
                    'published_at' => $record->published_at ?? Date::now(),
                ]);

                Notification::make()->success()->title("{$contentType} published")->send();
            });
    }
}
